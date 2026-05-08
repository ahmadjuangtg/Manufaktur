<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'Admin Gudang')->first();
        if ($role) {
            $role->update([
                'permissions' => ['dashboard_view', 'master_data_view', 'inventory_view', 'inventory_create', 'inventory_edit', 'inventory_delete']
            ]);
        }
    }
}
