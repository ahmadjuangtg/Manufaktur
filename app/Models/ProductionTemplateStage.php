<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionTemplateStage extends Model
{
    protected $fillable = ['template_id', 'name', 'sequence', 'machine_id'];

    public function template() { return $this->belongsTo(ProductionTemplate::class, 'template_id'); }
    public function machine() { return $this->belongsTo(Machine::class); }
    public function items() { return $this->hasMany(ProductionTemplateItem::class, 'stage_id'); }
}
