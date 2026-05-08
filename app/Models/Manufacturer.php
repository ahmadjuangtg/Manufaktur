<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model {
    protected $fillable = [
        'code', 'name', 'address', 'postal_code', 'province', 'city', 'district', 
        'sub_district', 'longitude', 'latitude', 'phone', 'email', 'website', 
        'contact_name', 'contact_phone', 'contact_email'
    ];

    public static function generateCode() {
        return 'MFG-' . str_pad(self::count() + 1, 3, '0', STR_PAD_LEFT);
    }
}
