<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Type;
use App\Models\Manufacturer;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Machine;
use Illuminate\Support\Facades\DB;

class MasterDataService
{
    // --- SUPPLIERS ---
    public function storeSupplier(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['code'] = Supplier::generateCode();
            $s = Supplier::create($data);
            if (isset($data['item_ids'])) {
                $s->items()->sync($data['item_ids']);
            }
            return $s;
        });
    }

    public function updateSupplier(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $s = Supplier::findOrFail($id);
            $s->update($data);
            if (isset($data['item_ids'])) {
                $s->items()->sync($data['item_ids']);
            } else {
                $s->items()->detach();
            }
            return $s;
        });
    }

    // --- ITEMS ---
    public function storeItem(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['code'] = Item::generateCode($data['category_id'], $data['type_id']);
            $data['display_name'] = $data['name'];
            
            // Sync legacy package_contain for compatibility with other modules
            if (isset($data['package_qty']) && isset($data['package_type'])) {
                $unit = Unit::find($data['unit_id']);
                $unitName = $unit ? $unit->name : '';
                $data['package_contain'] = $data['package_qty'] . ' ' . $unitName . ' / ' . $data['package_type'];
            }
            
            return Item::create($data);
        });
    }

    public function updateItem(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $item = Item::findOrFail($id);
            if ($item->category_id != $data['category_id'] || $item->type_id != $data['type_id']) {
                $data['code'] = Item::generateCode($data['category_id'], $data['type_id']);
            }
            $data['display_name'] = $data['name'];

            // Sync legacy package_contain for compatibility with other modules
            if (isset($data['package_qty']) && isset($data['package_type'])) {
                $unit = Unit::find($data['unit_id']);
                $unitName = $unit ? $unit->name : '';
                $data['package_contain'] = $data['package_qty'] . ' ' . $unitName . ' / ' . $data['package_type'];
            }

            $item->update($data);
            return $item;
        });
    }

    // --- CUSTOMERS ---
    public function storeCustomer(array $data)
    {
        $data['code'] = Customer::generateCode();
        return Customer::create($data);
    }

    public function updateCustomer(int $id, array $data)
    {
        $c = Customer::findOrFail($id);
        $c->update($data);
        return $c;
    }

    // --- MACHINES ---
    public function storeMachine(array $data)
    {
        $data['code'] = Machine::generateCode();
        return Machine::create($data);
    }

    public function updateMachine(int $id, array $data)
    {
        $m = Machine::findOrFail($id);
        $m->update($data);
        return $m;
    }

    // --- GENERIC CRUD HELPERS ---
    public function storeCategory(array $data) {
        return Category::create([
            'code' => Category::generateCode(), 
            'prefix' => strtoupper($data['prefix']), 
            'name' => $data['name']
        ]);
    }

    public function storeType(array $data) {
        return Type::create([
            'code' => Type::generateCode(), 
            'prefix' => strtoupper($data['prefix']), 
            'name' => $data['name']
        ]);
    }
}
