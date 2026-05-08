<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model {
    protected $fillable = ['code', 'prefix', 'name'];
    public static function generateCode() {
        return 'TYP-' . str_pad(self::count() + 1, 3, '0', STR_PAD_LEFT);
    }
}
