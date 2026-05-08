<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderStageItem extends Model
{
    protected $fillable = [
        'work_order_stage_id', 'item_id', 'quantity_per_batch', 
        'quantity_total', 'type'
    ];

    public function stage() { return $this->belongsTo(WorkOrderStage::class, 'work_order_stage_id'); }
    public function item() { return $this->belongsTo(Item::class); }
}
