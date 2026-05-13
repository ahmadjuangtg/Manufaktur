<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $fillable = ['code', 'name', 'machine_category_id', 'supplier_id', 'capacity', 'capacity_unit', 'output_unit', 'outlet', 'is_active'];

    public function category()
    {
        return $this->belongsTo(MachineCategory::class, 'machine_category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function steps()
    {
        return $this->hasMany(MachineStep::class)->orderBy('sequence');
    }

    protected static function booted()
    {
        static::creating(function ($machine) {
            if (!$machine->code) {
                $count = static::count() + 1;
                $machine->code = 'MAC-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function substitutes()
    {
        return $this->belongsToMany(Machine::class, 'machine_substitutions', 'machine_id', 'substitute_machine_id')
                    ->withPivot('notes')
                    ->withTimestamps();
    }

    public function capabilities()
    {
        return $this->belongsToMany(Item::class, 'machine_capabilities', 'machine_id', 'item_id')
                    ->withPivot('is_default', 'production_rate', 'output_unit', 'capacity_unit', 'thickness', 'diameter', 'cavity', 'cycle')
                    ->withTimestamps();
    }
}
