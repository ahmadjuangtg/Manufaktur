<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    protected $fillable = [
        'item_id', 'warehouse_id', 'current_stock', 'lock_stock', 'shadow_stock'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Accessor for available stock
    public function getAvailableStockAttribute()
    {
        return max(0, $this->current_stock - $this->lock_stock);
    }
}
