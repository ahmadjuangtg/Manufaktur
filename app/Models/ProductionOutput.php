<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOutput extends Model
{
    protected $fillable = ['work_order_id', 'work_order_stage_id', 'quantity_good', 'quantity_reject', 'operator_id', 'notes'];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function stage()
    {
        return $this->belongsTo(WorkOrderStage::class, 'work_order_stage_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
