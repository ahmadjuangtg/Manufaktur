<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineStatusLog extends Model
{
    protected $fillable = ['machine_id', 'work_order_id', 'status', 'reason', 'start_at', 'end_at'];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
