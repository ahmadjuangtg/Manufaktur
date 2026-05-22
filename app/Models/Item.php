<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class Item extends Model {
    use LogsActivity;
    protected $fillable = [
        'barcode', 'code', 'name', 'display_name', 'category_id', 'type_id', 
        'manufacturer_id', 'unit_id', 'package_qty', 'package_type', 
        'package_contain', 'length', 'width', 'height'
    ];
    public function category() { return $this->belongsTo(Category::class); }
    public function type() { return $this->belongsTo(Type::class); }
    public function manufacturer() { return $this->belongsTo(Manufacturer::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
    public static function generateCode($categoryId, $typeId) {
        $cat = Category::find($categoryId);
        $type = Type::find($typeId);
        $prefix = ($cat ? $cat->prefix : 'XXX') . '-' . ($type ? $type->prefix : 'XXX');
        $count = self::where('code', 'like', $prefix . '-%')->count() + 1;
        return $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
    public function suppliers() {
        return $this->belongsToMany(Supplier::class, 'item_supplier');
    }

    public function substitutes()
    {
        return $this->belongsToMany(Item::class, 'item_substitutions', 'item_id', 'substitute_item_id')
                    ->withPivot('conversion_ratio', 'notes')
                    ->withTimestamps();
    }

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }
}
