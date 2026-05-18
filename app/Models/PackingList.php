<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingList extends Model
{
    protected $fillable = [
        'packing_no', 'customer_id', 'delivery_batch_id', 'status', 'note', 'user_id'
    ];

    public function details()
    {
        return $this->hasMany(PackingListDetail::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryBatch()
    {
        return $this->belongsTo(DeliveryBatch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
