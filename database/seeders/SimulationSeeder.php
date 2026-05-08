<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Type;
use App\Models\Manufacturer;
use App\Models\Unit;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\ProductionTemplate;
use App\Models\ProductionTemplateProduct;
use App\Models\ProductionTemplateStage;
use App\Models\ProductionTemplateItem;
use App\Models\Machine;
use App\Models\WorkOrder;
use App\Models\StockTransaction;
use App\Models\StockOpname;
use App\Models\ItemRequest;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class SimulationSeeder extends Seeder
{
    public function run()
    {
        if (config('database.default') == 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        // 1. Basic Master Data
        $cat = Category::updateOrCreate(['prefix' => 'SIM'], ['name' => 'Simulation Category', 'code' => 'CAT-SIM-001']);
        $typeFG = Type::updateOrCreate(['prefix' => 'FIN'], ['name' => 'Barang Jadi', 'code' => 'TYP-FIN-001']);
        $typeRM = Type::updateOrCreate(['prefix' => 'RAW'], ['name' => 'Bahan Baku', 'code' => 'TYP-RAW-001']);
        $manu = Manufacturer::updateOrCreate(['name' => 'SimuCorp'], [
            'code' => 'MFG-SIM-001',
            'address' => 'Simulation Street No. 1',
            'phone' => '08123456789',
            'contact_name' => 'Simu Man',
            'contact_phone' => '08123456789'
        ]);
        $unit = Unit::updateOrCreate(['name' => 'PCS'], ['code' => 'UNT-SIM-001']);
        $wh = Warehouse::updateOrCreate(['name' => 'Main Warehouse'], [
            'address' => 'Simulation Address',
            'postal_code' => '12345',
            'province' => 'Simulation Province',
            'city' => 'Simulation City',
            'district' => 'Simulation District',
            'village' => 'Simulation Village',
            'region' => 'Simulation Region',
            'phone' => '08123456789',
            'warehouse_type' => 'Gudang Utama',
            'area' => 1000,
            'server_state' => 'WIB'
        ]);
        
        // 2. Items
        $product = Item::updateOrCreate(['code' => 'SIM-PROD-001'], [
            'name' => 'Simulation Product',
            'display_name' => 'Simulation Product',
            'barcode' => 'SIMPROD001',
            'category_id' => $cat->id,
            'type_id' => $typeFG->id,
            'manufacturer_id' => $manu->id,
            'unit_id' => $unit->id,
            'package_contain' => '1 Pcs/Box'
        ]);

        $material = Item::updateOrCreate(['code' => 'SIM-RAW-001'], [
            'name' => 'Simulation Raw Material',
            'display_name' => 'Simulation Raw Material',
            'barcode' => 'SIMRAW001',
            'category_id' => $cat->id,
            'type_id' => $typeRM->id,
            'manufacturer_id' => $manu->id,
            'unit_id' => $unit->id,
            'package_contain' => '1 Kg/Bag'
        ]);

        // 3. Machine
        $mCat = DB::table('machine_categories')->updateOrInsert(['code' => 'CAT-MCH-01'], ['name' => 'Machine Category Simulation', 'created_at' => now(), 'updated_at' => now()]);
        $mCatId = DB::table('machine_categories')->where('code', 'CAT-MCH-01')->value('id');

        $machine = Machine::updateOrCreate(['name' => 'SIM-MACHINE-01'], [
            'code' => 'MCH-SIM-01',
            'machine_category_id' => $mCatId,
            'capacity' => 100,
            'capacity_unit' => 'PCS/H',
            'is_active' => true
        ]);

        // 4. Production Template
        $tpl = ProductionTemplate::updateOrCreate(['name' => 'Simulasi Template'], [
            'code' => 'TPL-SIM-001',
            'product_id' => $product->id,
            'duration' => 2,
            'notes' => 'Simulation Template'
        ]);

        ProductionTemplateProduct::updateOrCreate([
            'template_id' => $tpl->id,
            'item_id' => $product->id
        ], ['quantity' => 1]);

        $stage = ProductionTemplateStage::updateOrCreate([
            'template_id' => $tpl->id,
            'name' => 'Tahapan Simulasi'
        ], [
            'sequence' => 1,
            'machine_id' => $machine->id
        ]);

        ProductionTemplateItem::updateOrCreate([
            'stage_id' => $stage->id,
            'item_id' => $material->id
        ], [
            'quantity_per_batch' => 0.5,
            'type' => 'input'
        ]);

        // 5. Initial Stock
        StockTransaction::create([
            'item_id' => $material->id,
            'warehouse_id' => $wh->id,
            'type' => 'IN',
            'quantity' => 1000,
            'reference_no' => 'INITIAL-STOCK',
            'user_id' => 1,
            'note' => 'Initial stock for simulation'
        ]);

        // 6. Stock Opname
        StockOpname::create([
            'item_id' => $material->id,
            'warehouse_id' => $wh->id,
            'system_qty' => 1000,
            'physical_qty' => 995,
            'difference' => -5,
            'status' => 'PENDING',
            'user_id' => 1,
            'note' => 'Simulation opname'
        ]);

        // 7. Order & Purchasing Flow
        $req = ItemRequest::create([
            'reference_no' => 'REQ-SIM-' . time(),
            'user_id' => 1,
            'warehouse_id' => $wh->id,
            'type_id' => $typeRM->id,
            'status' => 'APPROVED',
            'approved_by' => 1,
            'approved_at' => now()
        ]);

        DB::table('item_request_details')->insert([
            'item_request_id' => $req->id,
            'item_id' => $material->id,
            'quantity' => 500
        ]);

        $po = PurchaseOrder::create([
            'po_no' => 'PO-SIM-' . time(),
            'item_request_id' => $req->id,
            'supplier_id' => 1,
            'user_id' => 1,
            'order_date' => date('Y-m-d'),
            'status' => 'OPEN'
        ]);

        DB::table('purchase_order_details')->insert([
            'purchase_order_id' => $po->id,
            'item_id' => $material->id,
            'quantity' => 500,
            'received_quantity' => 0
        ]);

        if (config('database.default') == 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
