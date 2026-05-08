<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ItemRequest extends Model {
    protected $fillable = ['reference_no', 'user_id', 'warehouse_id', 'type_id', 'status', 'note', 'approved_by', 'approved_at', 'rejection_note'];
    public function type() { return $this->belongsTo(Type::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function details() { return $this->hasMany(ItemRequestDetail::class); }
    public static function generateRefNo() {
        return 'REQ-' . date('Ymd') . '-' . str_pad(self::count() + 1, 4, '0', STR_PAD_LEFT);
    }
}
