<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\Role::all() as $r) {
    echo "Role: " . $r->name . ' | Permissions: ' . json_encode($r->permissions) . "\n";
}

echo "\nSpecific Test for Aori Admin:\n";
$u = \App\Models\User::where('name', 'Aori Admin')->first();
if($u) {
    echo "User: " . $u->name . "\n";
    echo "Role: " . ($u->role->name ?? 'NONE') . "\n";
    echo "Perms Array: " . json_encode($u->role->permissions ?? []) . "\n";
    echo "Has master_data_view: " . ($u->hasPermission('master_data_view') ? 'YES' : 'NO') . "\n";
} else {
    echo "Aori Admin not found\n";
}


