<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutationDelivery extends Model
{
    protected $fillable = [
        'stock_mutation_id', 'item_id', 'quantity', 'delivered_by', 'delivered_at'
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
}
