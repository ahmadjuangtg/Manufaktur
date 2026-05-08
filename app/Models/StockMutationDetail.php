<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutationDetail extends Model
{
    protected $fillable = ['stock_mutation_id', 'item_id', 'quantity'];

    public function mutation()
    {
        return $this->belongsTo(StockMutation::class, 'stock_mutation_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
