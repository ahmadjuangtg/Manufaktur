<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Models\ProductionOutput;
use App\Models\Machine;
use App\Models\MachineStatusLog;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Warehouse;
use App\Models\Item;
use Carbon\Carbon;

class ShopFloorController extends Controller
{
    public function index()
    {
        $stages = WorkOrderStage::with([
                'workOrder' => function($q) {
                    $q->select('id', 'wo_number', 'customer_id', 'status')
                      ->with(['customer:id,name', 'mutations']);
                },
                'machine:id,name',
                'machine.steps:id,machine_id,name',
                'items.item:id,name,unit_id'
            ])
            ->whereHas('workOrder', function($q) {
                $q->whereIn('status', ['ready_to_production', 'in_progress']);
            })
            ->whereIn('status', ['pending', 'in_progress'])
            ->select('id', 'work_order_id', 'name', 'sequence', 'machine_id', 'status', 'start_time')
            ->orderBy('sequence')
            ->get();
            
        $machines = Machine::where('is_active', true)->get();
        $allWarehouses = Warehouse::all();
        $allItems = Item::with('unit')->get();
            
        return view('shop_floor.dashboard', compact('stages', 'machines', 'allWarehouses', 'allItems'));
    }

    public function startStage($id)
    {
        $stage = WorkOrderStage::findOrFail($id);
        
        DB::transaction(function () use ($stage) {
            $stage->update([
                'status' => 'in_progress',
                'start_time' => now()
            ]);

            // Update machine status
            if ($stage->machine_id) {
                MachineStatusLog::create([
                    'machine_id' => $stage->machine_id,
                    'work_order_id' => $stage->work_order_id,
                    'status' => 'RUNNING',
                    'start_at' => now()
                ]);
            }
            
            // Also update parent Work Order status to in_progress if it was ready_to_production
            if ($stage->workOrder->status === 'ready_to_production') {
                $stage->workOrder->update(['status' => 'in_progress']);
            }
        });

        return redirect()->back()->with('success', 'Tahapan produksi dimulai.');
    }

    public function updateMachineStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:RUNNING,DOWN,MAINTENANCE,IDLE',
            'reason' => 'nullable|string'
        ]);

        $machine = Machine::findOrFail($id);
        
        DB::transaction(function () use ($request, $machine) {
            // End previous log
            MachineStatusLog::where('machine_id', $machine->id)
                ->whereNull('end_at')
                ->update(['end_at' => now()]);

            // Create new log
            MachineStatusLog::create([
                'machine_id' => $machine->id,
                'status' => $request->status,
                'reason' => $request->reason,
                'start_at' => now()
            ]);
        });

        return redirect()->back()->with('success', 'Status mesin diperbarui.');
    }

    public function reportOutput(Request $request, $id)
    {
        $request->validate([
            'quantity_good' => 'required|numeric|min:0',
            'quantity_reject' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $stage = WorkOrderStage::findOrFail($id);

        ProductionOutput::create([
            'work_order_id' => $stage->work_order_id,
            'work_order_stage_id' => $stage->id,
            'quantity_good' => $request->quantity_good,
            'quantity_reject' => $request->quantity_reject,
            'operator_id' => Auth::id(),
            'notes' => $request->notes
        ]);

        return redirect()->back()->with('success', 'Hasil produksi (LHP) telah dicatat.');
    }

    public function finishStage($id)
    {
        $stage = WorkOrderStage::findOrFail($id);
        
        try {
            DB::transaction(function () use ($stage) {
                // MANDATORY CHECK: Must have at least one PHP or NPB created for this WO 
                // BEFORE we allow finishing the stage (as requested by user)
                $hasHandover = \App\Models\ProductionTransfer::where('work_order_id', $stage->work_order_id)->exists();
                if (!$hasHandover) {
                    throw new \Exception('Wajib mengisi form Serah Terima (PHP/NPB) terlebih dahulu sebelum menyelesaikan tahapan produksi.');
                }

                $stage->update([
                    'status' => 'completed',
                    'end_time' => now()
                ]);

                // End machine log
                if ($stage->machine_id) {
                    MachineStatusLog::where('machine_id', $stage->machine_id)
                        ->whereNull('end_at')
                        ->update(['end_at' => now()]);
                }

                // Check if all stages in this WO are completed
                $totalStages = WorkOrderStage::where('work_order_id', $stage->work_order_id)->count();
                $completedStages = WorkOrderStage::where('work_order_id', $stage->work_order_id)
                    ->where('status', 'completed')
                    ->count();
                    
                if ($totalStages === $completedStages) {
                    $stage->workOrder->update(['status' => 'completed']);
                }
            });

            return redirect()->back()->with('success', 'Tahapan produksi selesai.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function storeMaterialRequest(Request $request, $stageId)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $stage = WorkOrderStage::with('workOrder')->findOrFail($stageId);

        try {
            $inventoryService = app(\App\Services\InventoryService::class);
            $inventoryService->createMutationRequest([
                'work_order_id' => $stage->work_order_id,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'items' => $request->items,
                'note' => 'Requested from Shop Floor Dashboard for WO: ' . $stage->workOrder->wo_number
            ]);

            return redirect()->back()->with('success', 'Permintaan mutasi material berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat permintaan: ' . $e->getMessage());
        }
    }

    public function getStageItems($id)
    {
        $stage = WorkOrderStage::with(['items.item.unit', 'workOrder.stages.machine.warehouse'])->findOrFail($id);
        
        $items = $stage->items()->where('type', 'input')->get()->map(function($i) use ($stage) {
            // Logic: find if this item was an output of any PREVIOUS stage
            $sourceWarehouseId = null;
            $previousStages = $stage->workOrder->stages->where('sequence', '<', $stage->sequence)->sortByDesc('sequence');
            
            foreach ($previousStages as $prev) {
                $hasOutput = $prev->items->where('item_id', $i->item_id)->where('type', 'output')->first();
                if ($hasOutput && $prev->machine && $prev->machine->warehouse_id) {
                    $sourceWarehouseId = $prev->machine->warehouse_id;
                    break;
                }
            }

            return [
                'id' => $i->item_id,
                'name' => $i->item->name,
                'code' => $i->item->code,
                'quantity' => $i->quantity_total > 0 ? $i->quantity_total : ($i->quantity ?? 0),
                'unit' => $i->item->unit->name ?? 'UNIT',
                'suggested_source_warehouse_id' => $sourceWarehouseId
            ];
        });

        // Current machine's warehouse for destination
        $targetWarehouseId = $stage->machine->warehouse_id ?? null;

        return response()->json([
            'wo_number' => $stage->workOrder->wo_number,
            'items' => $items,
            'target_warehouse_id' => $targetWarehouseId
        ]);
    }
}
