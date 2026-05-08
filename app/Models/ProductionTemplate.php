<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionTemplate extends Model
{
    protected $fillable = [
        'code', 'name', 'product_id', 
        'production_line', 'marketing', 'duration',
        'stage_code', 'composition_code', 'notes'
    ];

    public function product() { return $this->belongsTo(Item::class, 'product_id'); }
    public function products() { return $this->hasMany(ProductionTemplateProduct::class, 'template_id'); }
    public function stages() { return $this->hasMany(ProductionTemplateStage::class, 'template_id')->orderBy('sequence'); }
}
