<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionTemplateItem extends Model
{
    protected $fillable = ['stage_id', 'item_id', 'quantity_per_batch', 'type'];

    public function stage() { return $this->belongsTo(ProductionTemplateStage::class, 'stage_id'); }
    public function item() { return $this->belongsTo(Item::class); }
}
