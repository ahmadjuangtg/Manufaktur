<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name', 'is_24_hours', 'operational_hours', 'server_state', 
        'address', 'postal_code', 'province', 'city', 'district', 
        'village', 'region', 'phone', 'warehouse_type', 'area', 'is_active'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_warehouse');
    }
}
