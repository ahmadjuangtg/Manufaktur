<?php

namespace App\Observers;

use App\Models\StockTransaction;
use App\Models\InventoryStock;

class StockTransactionObserver
{
    /**
     * Handle the StockTransaction "created" event.
     */
    public function created(StockTransaction $transaction): void
    {
        $this->updateStock($transaction, 1);
    }

    /**
     * Handle the StockTransaction "deleted" event.
     */
    public function deleted(StockTransaction $transaction): void
    {
        $this->updateStock($transaction, -1);
    }

    private function updateStock(StockTransaction $transaction, int $multiplier): void
    {
        $inventory = InventoryStock::firstOrCreate(
            [
                'item_id' => $transaction->item_id,
                'warehouse_id' => $transaction->warehouse_id,
            ],
            [
                'current_stock' => 0,
                'lock_stock' => 0,
                'shadow_stock' => 0,
            ]
        );

        $qty = $transaction->quantity * $multiplier;

        switch ($transaction->type) {
            case 'IN':
                $inventory->current_stock += $qty;
                break;
            case 'OUT':
                $inventory->current_stock -= $qty;
                break;
            case 'LOCK_IN':
                $inventory->lock_stock += $qty;
                break;
            case 'LOCK_OUT':
                $inventory->lock_stock -= $qty;
                break;
            case 'SHADOW_IN':
                $inventory->shadow_stock += $qty;
                break;
            case 'SHADOW_OUT':
                $inventory->shadow_stock -= $qty;
                break;
        }

        $inventory->save();
    }
}
