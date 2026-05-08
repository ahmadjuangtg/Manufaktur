<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model {
    protected $fillable = ['code', 'name'];
    public static function generateCode() {
        return 'UNT-' . str_pad(self::count() + 1, 3, '0', STR_PAD_LEFT);
    }
}
