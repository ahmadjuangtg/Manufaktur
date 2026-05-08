<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    protected $fillable = ['code', 'level', 'name', 'color'];

    public static function generateCode()
    {
        $last = self::latest()->first();
        $next = $last ? (int) substr($last->code, 4) + 1 : 1;
        return 'PRI-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
