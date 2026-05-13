<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\ProductionTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductionService
{
    /**
     * Create a Work Order from a Template
     */
    public function createFromTemplate(array $data)
    {
        return DB::transaction(function () use ($data) {
            $template = ProductionTemplate::with(['stages', 'products', 'items'])->findOrFail($data['template_id']);
            
            $wo = WorkOrder::create([
                'wo_number' => 'WO-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))),
                'production_template_id' => $template->id,
                'customer_id' => $data['customer_id'] ?? null,
                'production_line' => $data['production_line'] ?? 1,
                'status' => 'pending',
                'priority_id' => $data['priority_id'] ?? null,
                'total_batch' => $data['total_batch'] ?? 1,
                'note' => $data['note'] ?? null,
                'user_id' => Auth::id(),
            ]);

            // Copy products
            foreach ($template->products as $tp) {
                \App\Models\WorkOrderProduct::create([
                    'work_order_id' => $wo->id,
                    'item_id' => $tp->item_id,
                    'quantity' => $tp->quantity * $wo->total_batch,
                ]);
            }

            // Copy stages & items
            foreach ($template->stages as $ts) {
                $stage = \App\Models\WorkOrderStage::create([
                    'work_order_id' => $wo->id,
                    'name' => $ts->name,
                    'machine_id' => $ts->machine_id,
                    'sequence' => $ts->sequence,
                    'duration_hours' => $ts->duration_hours,
                ]);

                // Copy items for this stage
                $stageItems = $template->items->where('production_template_stage_id', $ts->id);
                foreach ($stageItems as $si) {
                    \App\Models\WorkOrderStageItem::create([
                        'work_order_stage_id' => $stage->id,
                        'item_id' => $si->item_id,
                        'quantity_per_batch' => $si->quantity,
                        'quantity_total' => $si->quantity * $wo->total_batch,
                        'type' => $si->type,
                    ]);
                }
            }

            return $wo;
        });
    }

    /**
     * Create a new Work Order manually
     */
    public function storeWorkOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $workOrder = WorkOrder::create([
                'wo_number' => $data['wo_number'],
                'production_line' => $data['production_line'],
                'production_date' => $data['production_date'],
                'customer_id' => $data['customer_id'],
                'marketing' => $data['marketing'] ?? null,
                'total_batch' => $data['total_batch'] ?? 1,
                'duration' => $data['duration'] ?? null,
                'stage_code' => $data['stage_code'] ?? null,
                'composition_code' => $data['composition_code'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending'
            ]);

            if (isset($data['products'])) {
                foreach ($data['products'] as $prod) {
                    \App\Models\WorkOrderProduct::create([
                        'work_order_id' => $workOrder->id,
                        'item_id' => $prod['item_id'],
                        'quantity' => $prod['quantity']
                    ]);
                }
            }

            if (isset($data['stages'])) {
                foreach ($data['stages'] as $index => $stageData) {
                    $stage = \App\Models\WorkOrderStage::create([
                        'work_order_id' => $workOrder->id,
                        'name' => $stageData['name'],
                        'sequence' => $index + 1,
                        'machine_id' => $stageData['machine_id'] ?? null,
                        'duration_hours' => $stageData['duration_hours'] ?? null,
                        'planned_start' => $stageData['planned_start'] ?? null,
                    ]);

                    if (isset($stageData['items'])) {
                        foreach ($stageData['items'] as $item) {
                            \App\Models\WorkOrderStageItem::create([
                                'work_order_stage_id' => $stage->id,
                                'item_id' => $item['item_id'],
                                'quantity_per_batch' => $item['quantity'],
                                'quantity_total' => $item['quantity'],
                                'type' => $item['type'] ?? 'MATERIAL'
                            ]);
                        }
                    }
                }
            }

            return $workOrder;
        });
    }

    /**
     * Update Work Order Status
     */
    public function updateStatus(int $id, string $status)
    {
        $wo = WorkOrder::findOrFail($id);
        $wo->update(['status' => $status]);
        return $wo;
    }
}
