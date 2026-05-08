<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineCategory extends Model
{
    protected $fillable = ['code', 'name'];

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    public static function generateCode()
    {
        return 'MC-' . str_pad(self::count() + 1, 3, '0', STR_PAD_LEFT);
    }
}
