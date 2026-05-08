<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::create([
            'name' => 'Super Administrator',
            'permissions' => ['all'],
        ]);

        User::create([
            'name' => 'Aori Admin',
            'email' => 'admin@aori.com',
            'password' => Hash::make('aori2026'),
            'role_id' => $adminRole->id,
        ]);
    }
}
