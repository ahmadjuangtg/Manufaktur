<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_id', 'username', 'name', 'country', 
        'address', 'phone', 'email', 'dob', 'gender'
    ];

    protected static function booted()
    {
        static::creating(function ($customer) {
            if (!$customer->customer_id) {
                $count = static::count() + 1;
                $customer->customer_id = 'C' . (40000000 + $count) . 'L';
            }
        });
    }
}
