<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ItemRequestDetail extends Model {
    protected $fillable = ['item_request_id', 'item_id', 'quantity'];
    public function request() { return $this->belongsTo(ItemRequest::class); }
    public function item() { return $this->belongsTo(Item::class); }
}
