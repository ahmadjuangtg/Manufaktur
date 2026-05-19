<?php
use App\Models\WorkOrder;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\Auth;

$wo = WorkOrder::where('wo_number', 'WO-20260516-0001')->with('stages.items')->first();
if ($wo && $wo->status === 'ready_to_production') {
    $mainWarehouseId = 2; // GUDANG BAHAN BAKU SOLO based on user screenshot (assuming ID 2)
    // Actually let's find it by name to be sure
    $wh = \App\Models\Warehouse::where('name', 'like', '%BAHAN BAKU SOLO%')->first();
    $warehouseId = $wh ? $wh->id : 1;

    foreach ($wo->stages as $stage) {
        foreach ($stage->items as $item) {
            if ($item->type === 'MATERIAL' || $item->type === 'input') {
                // Check if already locked
                $exists = StockTransaction::where('reference_no', $wo->wo_number)
                    ->where('item_id', $item->item_id)
                    ->where('type', 'LOCK_IN')
                    ->exists();
                
                if (!$exists) {
                    StockTransaction::create([
                        'warehouse_id' => $warehouseId,
                        'item_id' => $item->item_id,
                        'type' => 'LOCK_IN',
                        'quantity' => $item->quantity_total,
                        'reference_no' => $wo->wo_number,
                        'user_id' => 1, // Admin
                        'note' => 'Repair Booking WO: ' . $wo->wo_number
                    ]);
                    echo "Locked item {$item->item_id} for WO {$wo->wo_number}\n";
                }
            }
        }
    }
}
