<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $fillable = [
        'wo_number', 'production_line', 'production_date', 'customer_id', 'marketing',
        'total_batch', 'duration', 'stage_code', 'composition_code', 'notes', 'status',
        'priority_id', 'scheduled_start', 'scheduled_end'
    ];

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function products() { return $this->hasMany(WorkOrderProduct::class); }
    public function stages() { return $this->hasMany(WorkOrderStage::class)->orderBy('sequence'); }
    public function outputs() { return $this->hasMany(ProductionOutput::class); }
    public function transfers() { return $this->hasMany(ProductionTransfer::class); }
}
