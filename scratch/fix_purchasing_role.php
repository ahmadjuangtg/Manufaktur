<?php
use App\Models\Role;
$role = Role::where('name', 'Purchasing')->first();
if ($role) {
    $perms = $role->permissions ?? [];
    $new_perms = [
        'dashboard_view',
        'master_data_view',
        'order_view',
        'stock_card_view',
        'inventory_view'
    ];
    $role->permissions = array_unique(array_merge($perms, $new_perms));
    $role->save();
    echo "Purchasing role updated with extended permissions.\n";
}
