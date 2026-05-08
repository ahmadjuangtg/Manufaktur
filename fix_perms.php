<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;

try {
    $role = Role::where('name', 'Admin Gudang')->first();
    if ($role) {
        $perms = $role->permissions ?? [];
        if(!in_array('dashboard_view', $perms)) $perms[] = 'dashboard_view';
        if(!in_array('master data_view', $perms)) $perms[] = 'master data_view';
        $role->permissions = $perms;
        $role->save();
        echo "SUCCESS: Admin Gudang permissions updated.\n";
    } else {
        echo "ERROR: Role Admin Gudang not found.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
