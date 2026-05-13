<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Models\Machine;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchedulingService
{
    /**
     * Update a Work Order schedule and resequence the line(s)
     */
    public function updateWorkOrderSchedule(int $id, array $data)
    {
        return DB::transaction(function() use ($id, $data) {
            $wo = WorkOrder::findOrFail($id);
            $isInProgress = $wo->status === 'in_progress';

            // Fields that can ALWAYS be updated (e.g., timing)
            $updateData = [
                'scheduled_start' => $data['start'] ?? $wo->scheduled_start,
                'scheduled_end' => $data['end'] ?? $wo->scheduled_end,
                'production_line' => $data['production_line'] ?? $wo->production_line,
                'sort_order' => $data['sort_order'] ?? $wo->sort_order,
                'updated_at' => now()
            ];

            // Fields that are LOCKED if in_progress
            if (!$isInProgress) {
                if (isset($data['priority_id'])) $updateData['priority_id'] = $data['priority_id'];
                
                // Update Stage Machines
                if (isset($data['stage_machines']) && is_array($data['stage_machines'])) {
                    $this->updateStageMachines($wo, $data['stage_machines']);
                }

                // Update Product Items (Substitutions)
                if (isset($data['product_items']) && is_array($data['product_items'])) {
                    $this->updateProductItems($wo, $data['product_items']);
                }

                // Update Stage Items (Substitutions)
                if (isset($data['stage_items']) && is_array($data['stage_items'])) {
                    $this->updateStageItems($wo, $data['stage_items']);
                }
            }

            $wo->update($updateData);
            return $wo;
        });
    }

    /**
     * Update items in Stage Items (Substitutions)
     */
    protected function updateStageItems(WorkOrder $wo, array $stageItems)
    {
        foreach ($stageItems as $stageItemId => $newItemId) {
            DB::table('work_order_stage_items')
                ->where('id', $stageItemId)
                ->update(['item_id' => $newItemId]);
        }
    }

    /**
     * Update items in WO Products (Substitutions)
     */
    protected function updateProductItems(WorkOrder $wo, array $productItems)
    {
        foreach ($productItems as $woProductId => $newItemId) {
            DB::table('work_order_products')
                ->where('id', $woProductId)
                ->where('work_order_id', $wo->id)
                ->update(['item_id' => $newItemId]);
        }
    }

    /**
     * Efficiently update machines and recalculate durations
     */
    protected function updateStageMachines(WorkOrder $wo, array $stageMachines)
    {
        $totalDuration = 0;
        $machineIds = array_values($stageMachines);
        $machines = Machine::whereIn('id', $machineIds)->get()->keyBy('id');
        $stageIds = array_keys($stageMachines);
        $stages = WorkOrderStage::whereIn('id', $stageIds)->get()->keyBy('id');
        
        $totalQty = DB::table('work_order_products')->where('work_order_id', $wo->id)->sum('quantity');
        $totalBatch = $wo->total_batch ?? 1;
        $grandTotalQty = $totalQty * $totalBatch;

        foreach ($stageMachines as $stageId => $machineId) {
            $newMachine = $machines->get($machineId);
            $stage = $stages->get($stageId);
            
            if ($newMachine && $stage && $newMachine->capacity > 0) {
                $unit = strtolower($newMachine->capacity_unit ?? '');
                $hours = (str_contains($unit, 'menit') || str_contains($unit, 'min')) 
                    ? ($grandTotalQty / ($newMachine->capacity * 60)) 
                    : ($grandTotalQty / $newMachine->capacity);
                
                $stageHours = round($hours, 2);
                
                DB::table('work_order_stages')
                    ->where('id', $stageId)
                    ->update([
                        'machine_id' => $machineId,
                        'duration_hours' => $stageHours
                    ]);
                $totalDuration += $stageHours;
            } else if ($stage) {
                $totalDuration += $stage->duration_hours;
            }
        }
        
        $wo->update(['duration' => $totalDuration]);
    }

    /**
     * Resource-Aware Parallel Resequencing
     */
    public function resequenceLine($line)
    {
        if (!$line) return;

        // 1. Build Global Machine Availability Map across ALL lines
        // We need to know what's happening everywhere to handle machine conflicts
        $allActiveWOs = WorkOrder::with('stages')
            ->whereIn('status', ['ready_to_production', 'in_progress'])
            ->whereNotNull('scheduled_start')
            ->leftJoin('priorities', 'work_orders.priority_id', '=', 'priorities.id')
            ->select('work_orders.*')
            // Priority & Sort Order globally
            ->orderByRaw('CASE WHEN priorities.level IS NULL THEN 999 ELSE priorities.level END ASC')
            ->orderByRaw('CASE WHEN work_orders.sort_order = 0 OR work_orders.sort_order IS NULL THEN 9999 ELSE work_orders.sort_order END ASC')
            ->orderBy('work_orders.updated_at', 'desc')
            ->orderBy('work_orders.id', 'desc')
            ->get();

        if ($allActiveWOs->isEmpty()) return;

        // Determine Global Anchor (earliest scheduled start across all lines)
        $globalAnchor = $allActiveWOs->min('scheduled_start');
        $machineAvailability = [];

        foreach ($allActiveWOs as $wo) {
            $duration = $wo->duration > 0 ? $wo->duration : 1;
            $originalStart = Carbon::parse($wo->scheduled_start);
            
            // Earliest possible start based on machines used
            $earliestStart = Carbon::parse($globalAnchor);
            foreach ($wo->stages as $stage) {
                if ($stage->machine_id && isset($machineAvailability[$stage->machine_id])) {
                    if ($machineAvailability[$stage->machine_id]->gt($earliestStart)) {
                        $earliestStart = $machineAvailability[$stage->machine_id]->copy();
                    }
                }
            }

            // Determine New Start
            // Logic: 
            // 1. If it's the very first WO in its line, snap to its line's earliest available time or machines
            // 2. Otherwise, check original start vs machine availability
            $newStart = $originalStart->gt($earliestStart) ? $originalStart : $earliestStart;
            
            // SPECIAL CASE: If it's the TOP priority/sort on ITS line, we can allow it to move earlier to the global anchor
            // if no machine conflicts exist.
            $lineWOs = $allActiveWOs->where('production_line', $wo->production_line);
            if ($lineWOs->first()->id == $wo->id) {
                $newStart = $earliestStart; 
            }

            $newEnd = $newStart->copy()->addMinutes($duration * 60);

            // Update availability map
            foreach ($wo->stages as $stage) {
                if ($stage->machine_id) {
                    $machineAvailability[$stage->machine_id] = $newEnd->copy();
                }
            }

            // ONLY UPDATE if it's the line we are currently resequencing
            // OR if it's an affected WO that changed due to global shift
            if ($wo->scheduled_start != $newStart->toDateTimeString()) {
                DB::table('work_orders')->where('id', $wo->id)->update([
                    'scheduled_start' => $newStart->toDateTimeString(),
                    'scheduled_end' => $newEnd->toDateTimeString()
                ]);
            }
        }

        // 2. Final pass for Sort Order cleanup on the specific line
        $this->updateLineSortOrder($line);
    }

    protected function updateLineSortOrder($line)
    {
        $wos = WorkOrder::where('production_line', $line)
            ->whereIn('status', ['ready_to_production', 'in_progress'])
            ->leftJoin('priorities', 'work_orders.priority_id', '=', 'priorities.id')
            ->select('work_orders.id', 'work_orders.sort_order')
            ->orderByRaw('CASE WHEN priorities.level IS NULL THEN 999 ELSE priorities.level END ASC')
            ->orderByRaw('CASE WHEN work_orders.sort_order = 0 OR work_orders.sort_order IS NULL THEN 9999 ELSE work_orders.sort_order END ASC')
            ->orderBy('work_orders.updated_at', 'desc')
            ->orderBy('work_orders.id', 'desc')
            ->get();

        foreach ($wos as $index => $wo) {
            $newSort = $index + 1;
            if ($wo->sort_order != $newSort) {
                DB::table('work_orders')->where('id', $wo->id)->update(['sort_order' => $newSort]);
            }
        }
    }
}
