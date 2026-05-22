<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class StockTransaction extends Model
{
    use LogsActivity;
    protected $fillable = ['item_id', 'warehouse_id', 'type', 'quantity', 'reference_no', 'note', 'user_id'];

    public function item() { return $this->belongsTo(Item::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function user() { return $this->belongsTo(User::class); }
}
