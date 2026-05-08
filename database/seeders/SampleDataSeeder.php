<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Manufacturer;
use App\Models\Item;
use App\Models\Type;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Warehouse
        $warehouse = Warehouse::updateOrCreate(
            ['name' => 'AORI'],
            [
                'address' => 'Solo, Jawa Tengah',
                'server_state' => 'WIB',
                'postal_code' => '57123',
                'province' => 'Jawa Tengah',
                'city' => 'Solo',
                'district' => 'Laweyan',
                'village' => 'Pajang',
                'region' => 'Solo Raya',
                'phone' => '0271-123456',
                'warehouse_type' => 'Gudang WIP',
                'area' => 500.00
            ]
        );

        // 2. Category
        $category = Category::updateOrCreate(
            ['name' => 'WIP'],
            ['code' => 'WIP', 'prefix' => 'WIP']
        );

        // 3. Unit
        $unit = Unit::updateOrCreate(
            ['name' => 'Roll'],
            ['code' => 'ROL']
        );

        // 4. Type
        $type = Type::updateOrCreate(
            ['name' => 'Film'],
            ['code' => 'FLM', 'prefix' => 'FLM']
        );

        // 5. Manufacturer (From Image 3)
        $manufacturer = Manufacturer::updateOrCreate(
            ['name' => 'MILLIKEN'],
            [
                'code' => 'MLK',
                'address' => 'LAKSAMANA YOS SUDARSO KAWASAN PERGUDANGAN TUNAS BLOK KEBON BESAR BATUCEPER',
                'phone' => '628119111748',
                'email' => 'daniel.tanzil@milliken.com',
                'province' => 'Banten',
                'city' => 'Tangerang',
                'district' => 'Batuceper',
                'sub_district' => 'Kebon Besar',
                'contact_name' => 'Mr. Daniel Tanzil',
                'contact_phone' => '628119111748'
            ]
        );

        // 6. Items (From Image 1)
        $items = [
            [
                'code' => 'E0202072025T',
                'barcode' => 'E0202072025T',
                'name' => 'PET FILM TRANS 0.20 MM X 720 MM X 250 M',
                'package_contain' => '152.04 Kg/Roll'
            ],
            [
                'code' => 'E0203045035T',
                'barcode' => 'E0203045035T',
                'name' => 'PET FILM TRANS 0.30 MM X 450 MM X 350 M',
                'package_contain' => '139.37 Kg/Roll'
            ],
            [
                'code' => 'E0112070020T',
                'barcode' => 'E0112070020T',
                'name' => 'PP FILM TRANS 1.20 MM X 700 MM X 200 M',
                'package_contain' => '152.04 Kg/Roll'
            ]
        ];

        foreach ($items as $itemData) {
            Item::updateOrCreate(
                ['code' => $itemData['code']],
                array_merge($itemData, [
                    'display_name' => $itemData['name'],
                    'category_id' => $category->id,
                    'type_id' => $type->id,
                    'manufacturer_id' => $manufacturer->id,
                    'unit_id' => $unit->id,
                    'length' => 0,
                    'width' => 0,
                    'height' => 0
                ])
            );
        }
    }
}
