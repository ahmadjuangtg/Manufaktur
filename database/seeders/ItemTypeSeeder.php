<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Type;

class ItemTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Bahan Baku', 'code' => 'RAW', 'prefix' => 'RAW'],
            ['name' => 'Bahan Pembantu', 'code' => 'AUX', 'prefix' => 'AUX'],
            ['name' => 'Barang Jadi', 'code' => 'FIN', 'prefix' => 'FIN'],
            ['name' => 'Barang WIP', 'code' => 'WIP', 'prefix' => 'WIP'],
            ['name' => 'Sparepart', 'code' => 'SPR', 'prefix' => 'SPR'],
            ['name' => 'Tools', 'code' => 'TLS', 'prefix' => 'TLS'],
            ['name' => 'Consumables', 'code' => 'CON', 'prefix' => 'CON'],
            ['name' => 'Office Supplies', 'code' => 'OFF', 'prefix' => 'OFF'],
        ];

        foreach ($types as $type) {
            Type::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
