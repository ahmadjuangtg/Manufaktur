<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderProduct extends Model
{
    protected $fillable = ['work_order_id', 'item_id', 'quantity'];

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function item() { return $this->belongsTo(Item::class); }
}
