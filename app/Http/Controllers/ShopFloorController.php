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
use Carbon\Carbon;

class ShopFloorController extends Controller
{
    public function index()
    {
        // Tuning: Selective column loading for production stages
        $stages = WorkOrderStage::with([
                'workOrder' => function($q) {
                    $q->select('id', 'wo_number', 'customer_id', 'status')
                      ->with('customer:id,name');
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
            
        $machines = Machine::select('id', 'name', 'code', 'is_active')->where('is_active', true)->get();
            
        return view('shop_floor.dashboard', compact('stages', 'machines'));
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
        
        DB::transaction(function () use ($stage) {
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
                // If this was the last stage, the WO is not necessarily 'completed' 
                // until PHP is verified, but we can set it to 'finishing' or similar
                // For now, keep it 'in_progress' or set to 'completed' as per current logic
                $stage->workOrder->update(['status' => 'completed']);
            }
        });

        return redirect()->back()->with('success', 'Tahapan produksi selesai.');
    }
}
