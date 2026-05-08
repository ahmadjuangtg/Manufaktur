<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrder extends Model {
    protected $fillable = ['po_no', 'item_request_id', 'supplier_id', 'user_id', 'status', 'order_date', 'total_amount'];
    public function request() { return $this->belongsTo(ItemRequest::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function details() { return $this->hasMany(PurchaseOrderDetail::class); }
    public static function generatePONo() {
        return 'PO-' . date('Ymd') . '-' . str_pad(self::count() + 1, 4, '0', STR_PAD_LEFT);
    }
}
