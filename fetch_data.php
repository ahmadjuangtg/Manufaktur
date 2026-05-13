<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Manufacturer;
use App\Models\Category;
use App\Models\Type;
use App\Models\Unit;

$data = [
    'manufacturers' => Manufacturer::all(['id', 'name']),
    'categories' => Category::all(['id', 'name']),
    'types' => Type::all(['id', 'name']),
    'units' => Unit::all(['id', 'name']),
];

echo json_encode($data, JSON_PRETTY_PRINT);
