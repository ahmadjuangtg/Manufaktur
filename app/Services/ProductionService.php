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
                'status' => 'draft',
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
                'status' => 'draft'
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
        $wo = WorkOrder::with(['stages.items', 'products'])->findOrFail($id);
        $oldStatus = $wo->status;
        
        if ($oldStatus === $status) return $wo;

        return DB::transaction(function() use ($wo, $status, $oldStatus) {
            $wo->update(['status' => $status]);
            $mainWarehouseId = 1; // Default warehouse logic
            
            // DRAFT -> READY_TO_PRODUCTION: LOCK MATERIALS
            if ($status === 'ready_to_production' && in_array($oldStatus, ['draft', 'pending'])) {
                foreach ($wo->stages as $stage) {
                    foreach ($stage->items as $item) {
                        if ($item->type === 'MATERIAL') {
                            \App\Models\StockTransaction::create([
                                'warehouse_id' => $mainWarehouseId,
                                'item_id' => $item->item_id,
                                'type' => 'LOCK_IN',
                                'quantity' => $item->quantity_total,
                                'reference_no' => $wo->wo_number,
                                'user_id' => Auth::id(),
                                'note' => 'Booking WO: ' . $wo->wo_number
                            ]);
                        }
                    }
                }
            }

            // READY -> DRAFT (Cancel Booking): UNLOCK MATERIALS
            if ($status === 'draft' && $oldStatus === 'ready_to_production') {
                foreach ($wo->stages as $stage) {
                    foreach ($stage->items as $item) {
                        if ($item->type === 'MATERIAL') {
                            \App\Models\StockTransaction::create([
                                'warehouse_id' => $mainWarehouseId,
                                'item_id' => $item->item_id,
                                'type' => 'LOCK_OUT',
                                'quantity' => $item->quantity_total,
                                'reference_no' => $wo->wo_number,
                                'user_id' => Auth::id(),
                                'note' => 'Batal Booking WO: ' . $wo->wo_number
                            ]);
                        }
                    }
                }
            }

            // READY -> IN_PROGRESS: SHADOW FINISHED GOODS
            if ($status === 'in_progress' && in_array($oldStatus, ['ready_to_production', 'pending'])) {
                foreach ($wo->products as $prod) {
                    \App\Models\StockTransaction::create([
                        'warehouse_id' => $mainWarehouseId,
                        'item_id' => $prod->item_id,
                        'type' => 'SHADOW_IN',
                        'quantity' => $prod->quantity,
                        'reference_no' => $wo->wo_number,
                        'user_id' => Auth::id(),
                        'note' => 'Ekspektasi Hasil WO: ' . $wo->wo_number
                    ]);
                }
            }

            // IN_PROGRESS -> COMPLETED: CONSUME MATERIALS AND PRODUCE FINISHED GOODS
            if ($status === 'completed' && $oldStatus === 'in_progress') {
                // 1. Consume Materials (Release Lock + Physical OUT)
                foreach ($wo->stages as $stage) {
                    foreach ($stage->items as $item) {
                        if ($item->type === 'MATERIAL') {
                            // Find lock
                            $lock = \App\Models\StockTransaction::where('reference_no', $wo->wo_number)
                                ->where('item_id', $item->item_id)
                                ->where('type', 'LOCK_IN')
                                ->first();
                            
                            if ($lock) {
                                \App\Models\StockTransaction::create([
                                    'warehouse_id' => $lock->warehouse_id,
                                    'item_id' => $item->item_id,
                                    'type' => 'LOCK_OUT',
                                    'quantity' => $item->quantity_total,
                                    'reference_no' => $wo->wo_number,
                                    'user_id' => Auth::id(),
                                    'note' => 'Realisasi Produksi WO: ' . $wo->wo_number
                                ]);
                            }

                            // Decrease Physical
                            \App\Models\StockTransaction::create([
                                'warehouse_id' => $lock ? $lock->warehouse_id : $mainWarehouseId,
                                'item_id' => $item->item_id,
                                'type' => 'OUT',
                                'quantity' => $item->quantity_total,
                                'reference_no' => $wo->wo_number,
                                'user_id' => Auth::id(),
                                'note' => 'Pemakaian Bahan Baku WO: ' . $wo->wo_number
                            ]);
                        }
                    }
                }

                // 2. Realize Finished Goods (Release Shadow)
                // Note: The physical IN is handled by verifyHandover in ProductionReportController 
                // if they strictly use the handover module. But if they don't, we should add it here.
                // Assuming Aori uses Handover module for FG receipt:
                foreach ($wo->products as $prod) {
                    $shadow = \App\Models\StockTransaction::where('reference_no', $wo->wo_number)
                        ->where('item_id', $prod->item_id)
                        ->where('type', 'SHADOW_IN')
                        ->first();
                        
                    if ($shadow) {
                        \App\Models\StockTransaction::create([
                            'warehouse_id' => $shadow->warehouse_id,
                            'item_id' => $prod->item_id,
                            'type' => 'SHADOW_OUT',
                            'quantity' => $prod->quantity,
                            'reference_no' => $wo->wo_number,
                            'user_id' => Auth::id(),
                            'note' => 'Realisasi Hasil WO: ' . $wo->wo_number
                        ]);
                    }
                }
            }

            return $wo;
        });
    }
}
