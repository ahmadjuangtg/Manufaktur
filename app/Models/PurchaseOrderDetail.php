<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrderDetail extends Model {
    protected $fillable = ['purchase_order_id', 'item_id', 'quantity', 'received_quantity', 'price'];
    public function order() { return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id'); }
    public function item() { return $this->belongsTo(Item::class); }
}
