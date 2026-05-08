<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {
    protected $fillable = ['code', 'prefix', 'name'];
    public static function generateCode() {
        return 'CAT-' . str_pad(self::count() + 1, 3, '0', STR_PAD_LEFT);
    }
}
