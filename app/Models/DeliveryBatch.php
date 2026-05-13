<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryBatch extends Model
{
    protected $fillable = [
        'batch_no', 'destination', 'driver_name', 'vehicle_no', 
        'status', 'departure_at', 'arrival_at', 'note', 'user_id'
    ];

    public function packingLists()
    {
        return $this->hasMany(PackingList::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
