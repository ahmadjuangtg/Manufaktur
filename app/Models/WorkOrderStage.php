<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderStage extends Model
{
    protected $fillable = [
        'work_order_id', 'name', 'sequence', 'machine_id', 'duration_hours',
        'planned_start', 'start_time', 'end_time', 'status'
    ];

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function machine() { return $this->belongsTo(Machine::class); }
    public function items() { return $this->hasMany(WorkOrderStageItem::class); }
    public function outputs() { return $this->hasMany(ProductionOutput::class); }
}
