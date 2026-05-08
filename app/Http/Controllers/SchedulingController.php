<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Priority;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SchedulingController extends Controller
{
    public function index()
    {
        $workOrders = WorkOrder::with(['priority', 'customer', 'stages.machine', 'stages.items.item'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('priority_id', 'desc')
            ->get();
            
        $priorities = Priority::all();
        
        return view('production.scheduling.index', compact('workOrders', 'priorities'));
    }

    public function updateSchedule(Request $request, $id)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'priority_id' => 'nullable|exists:priorities,id',
            'stage_machines' => 'nullable|array',
            'item_substitutions' => 'nullable|array'
        ]);

        $wo = WorkOrder::with('stages')->findOrFail($id);
        
        try {
            \Illuminate\Support\Facades\Log::info('Starting Schedule Update', ['id' => $id, 'data' => $request->all()]);
            
            \Illuminate\Support\Facades\DB::transaction(function() use ($request, $wo) {
                $wo->update([
                    'scheduled_start' => $request->start,
                    'scheduled_end' => $request->end,
                    'priority_id' => $request->priority_id
                ]);

                // Update Stage Machines & Recalculate Duration
                if ($request->has('stage_machines')) {
                    foreach ($request->stage_machines as $stageId => $machineId) {
                        \Illuminate\Support\Facades\Log::info('Updating machine for stage', ['stage_id' => $stageId, 'machine_id' => $machineId]);
                        $stage = $wo->stages->find($stageId);
                        if ($stage) {
                            $oldMachineId = $stage->machine_id;
                            $stage->machine_id = $machineId;
                            
                            if ($oldMachineId != $machineId) {
                                $newMachine = \App\Models\Machine::find($machineId);
                                if ($newMachine && $newMachine->capacity > 0) {
                                    $totalQty = \Illuminate\Support\Facades\DB::table('work_order_products')->where('work_order_id', $wo->id)->sum('quantity');
                                    $totalBatch = $wo->total_batch ?? 1;
                                    $grandTotalQty = $totalQty * $totalBatch;
                                    
                                    $unit = strtolower($newMachine->capacity_unit ?? '');
                                    if (str_contains($unit, 'menit') || str_contains($unit, 'min')) {
                                        $hours = $grandTotalQty / ($newMachine->capacity * 60);
                                    } else {
                                        $hours = $grandTotalQty / $newMachine->capacity;
                                    }
                                    $stage->duration_hours = round($hours, 2);
                                    \Illuminate\Support\Facades\Log::info('Recalculated duration', ['hours' => $stage->duration_hours]);
                                }
                            }
                            $stage->save();
                        }
                    }
                }

                // Update Item Substitutions
                if ($request->has('item_substitutions')) {
                    foreach ($request->item_substitutions as $stageItemId => $itemId) {
                        \Illuminate\Support\Facades\Log::info('Updating item substitution', ['stage_item_id' => $stageItemId, 'item_id' => $itemId]);
                        \Illuminate\Support\Facades\DB::table('work_order_stage_items')->where('id', $stageItemId)->update(['item_id' => $itemId]);
                    }
                }
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Scheduling Error: ' . $e->getMessage(), [
                'id' => $id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getSubstitutes(Request $request)
    {
        $type = $request->type;
        $id = $request->id;

        if ($type === 'machine') {
            $machine = \App\Models\Machine::find($id);
            if (!$machine) return response()->json([]);
            return response()->json($machine->substitutes);
        }

        if ($type === 'item') {
            $item = \App\Models\Item::find($id);
            if (!$item) return response()->json([]);
            return response()->json($item->substitutes);
        }

        return response()->json([]);
    }
}
