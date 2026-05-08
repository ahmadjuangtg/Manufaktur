<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

try {
    $role = Role::firstOrCreate(['name' => 'Super Administrator'], ['permissions' => ['all']]);
    User::updateOrCreate(
        ['email' => 'admin@aori.com'],
        [
            'name' => 'Aori Admin',
            'password' => Hash::make('aori2026'),
            'role_id' => $role->id
        ]
    );
    echo "SUCCESS: Admin account created/updated.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
