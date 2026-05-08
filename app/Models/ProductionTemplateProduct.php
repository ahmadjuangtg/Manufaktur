<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionTemplateProduct extends Model
{
    protected $fillable = ['template_id', 'item_id', 'quantity'];

    public function template() { return $this->belongsTo(ProductionTemplate::class); }
    public function item() { return $this->belongsTo(Item::class); }
}
