<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Supplier extends Model {
    protected $fillable = [
        'code', 'name', 'address', 'postal_code', 'province', 'city', 'district', 
        'sub_district', 'longitude', 'latitude', 'phone', 'email', 'website', 
        'contact_name', 'contact_phone', 'contact_email'
    ];
    public static function generateCode() {
        return 'SUP-' . str_pad(self::count() + 1, 3, '0', STR_PAD_LEFT);
    }
    public function items() {
        return $this->belongsToMany(Item::class, 'item_supplier');
    }
}
