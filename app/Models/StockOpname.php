<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $fillable = [
        'item_id', 
        'warehouse_id', 
        'system_qty', 
        'physical_qty', 
        'difference', 
        'status', 
        'rejection_reason',
        'note', 
        'user_id', 
        'approved_by', 
        'approved_at'
    ];

    public function item() { return $this->belongsTo(Item::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
