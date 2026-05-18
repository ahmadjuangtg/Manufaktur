<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionTransfer extends Model
{
    protected $fillable = [
        'reference_no', 'work_order_id', 'work_order_stage_id', 'type', 'quantity', 
        'from_warehouse_id', 'to_warehouse_id', 'status', 
        'user_id', 'verified_by', 'verified_at'
    ];

    public function stage()
    {
        return $this->belongsTo(WorkOrderStage::class, 'work_order_stage_id');
    }

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
