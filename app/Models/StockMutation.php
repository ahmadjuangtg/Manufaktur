<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class StockMutation extends Model
{
    use LogsActivity;
    protected $fillable = [
        'reference_no', 'work_order_id', 'from_warehouse_id', 'to_warehouse_id', 'status', 
        'note', 'rejection_reason', 'user_id', 'approved_by', 'sent_by', 'received_by',
        'approved_at', 'sent_at', 'received_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function details()
    {
        return $this->hasMany(StockMutationDetail::class);
    }

    public function deliveries()
    {
        return $this->hasMany(StockMutationDelivery::class);
    }
}
