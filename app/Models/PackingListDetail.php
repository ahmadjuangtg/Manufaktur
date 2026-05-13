<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingListDetail extends Model
{
    protected $fillable = [
        'packing_list_id', 'item_id', 'quantity', 'package_type', 'package_number'
    ];

    public function packingList()
    {
        return $this->belongsTo(PackingList::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
