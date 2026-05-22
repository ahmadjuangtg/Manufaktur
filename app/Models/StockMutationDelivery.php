<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutationDelivery extends Model
{
    protected $fillable = [
        'stock_mutation_id', 'item_id', 'quantity', 'delivered_by', 'delivered_at',
        'shipment_no', 'received_quantity', 'received_by', 'received_at'
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function mutation()
    {
        return $this->belongsTo(StockMutation::class, 'stock_mutation_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
