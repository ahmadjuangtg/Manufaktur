<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Type;
use App\Models\Manufacturer;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;

class MasterController extends Controller 
{
    protected $masterService;

    public function __construct(\App\Services\MasterDataService $masterService)
    {
        $this->masterService = $masterService;
    }

    // SUPPLIERS
    public function indexSupplier(Request $request) { 
        $query = Supplier::query()->with('items');
        if ($request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }
        return view('master.suppliers.index', [
            'data' => $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString(),
            'items' => Item::select('id', 'name')->get(),
            'search' => $request->search
        ]); 
    }
    public function storeSupplier(Request $request) {
        $request->validate(['name' => 'required']);
        $this->masterService->storeSupplier($request->all());
        return redirect()->back()->with('success', 'Supplier created.');
    }
    public function updateSupplier(Request $request, $id) {
        $request->validate(['name' => 'required']);
        $this->masterService->updateSupplier($id, $request->all());
        return redirect()->back()->with('success', 'Supplier updated.');
    }
    public function destroySupplier($id) {
        Supplier::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Supplier deleted.');
    }

    public function getSupplierItems($id) {
        $supplier = Supplier::with('items')->findOrFail($id);
        return response()->json($supplier->items->pluck('id'));
    }

    // CATEGORIES
    public function indexCategory(Request $request) { 
        $query = Category::query();
        if ($request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('prefix', 'LIKE', "%{$request->search}%");
        }
        return view('master.categories.index', [
            'data' => $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString(),
            'search' => $request->search
        ]); 
    }
    public function storeCategory(Request $request) {
        $request->validate(['prefix' => 'required|unique:categories', 'name' => 'required']);
        $this->masterService->storeCategory($request->all());
        return redirect()->back()->with('success', 'Category created.');
    }
    public function updateCategory(Request $request, $id) {
        $category = Category::findOrFail($id);
        $request->validate(['prefix' => 'required|unique:categories,prefix,'.$id, 'name' => 'required']);
        $category->update(['prefix' => strtoupper($request->prefix), 'name' => $request->name]);
        return redirect()->back()->with('success', 'Category updated.');
    }
    public function destroyCategory($id) {
        Category::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Category deleted.');
    }

    // TYPES
    public function indexType(Request $request) { 
        $query = Type::query();
        if ($request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('prefix', 'LIKE', "%{$request->search}%");
        }
        return view('master.types.index', [
            'data' => $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString(),
            'search' => $request->search
        ]); 
    }
    public function storeType(Request $request) {
        $request->validate(['prefix' => 'required|unique:types', 'name' => 'required']);
        $this->masterService->storeType($request->all());
        return redirect()->back()->with('success', 'Type created.');
    }
    public function updateType(Request $request, $id) {
        $type = Type::findOrFail($id);
        $request->validate(['prefix' => 'required|unique:types,prefix,'.$id, 'name' => 'required']);
        $type->update(['prefix' => strtoupper($request->prefix), 'name' => $request->name]);
        return redirect()->back()->with('success', 'Type updated.');
    }
    public function destroyType($id) {
        Type::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Type deleted.');
    }

    // MANUFACTURERS
    public function indexManufacturer(Request $request) { 
        $query = Manufacturer::query();
        if ($request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('code', 'LIKE', "%{$request->search}%");
        }
        return view('master.manufacturers.index', [
            'data' => $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString(),
            'search' => $request->search
        ]); 
    }
    public function storeManufacturer(Request $request) {
        $request->validate(['name' => 'required']);
        $data = $request->all();
        $data['code'] = Manufacturer::generateCode();
        Manufacturer::create($data);
        return redirect()->back()->with('success', 'Manufacturer created.');
    }
    public function updateManufacturer(Request $request, $id) {
        $m = Manufacturer::findOrFail($id);
        $request->validate(['name' => 'required']);
        $m->update($request->all());
        return redirect()->back()->with('success', 'Manufacturer updated.');
    }
    public function destroyManufacturer($id) {
        Manufacturer::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Manufacturer deleted.');
    }

    // UNITS
    public function indexUnit(Request $request) { 
        $query = Unit::query();
        if ($request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('code', 'LIKE', "%{$request->search}%");
        }
        return view('master.units.index', [
            'data' => $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString(),
            'search' => $request->search
        ]); 
    }
    public function storeUnit(Request $request) {
        $request->validate(['name' => 'required']);
        Unit::create(['code' => Unit::generateCode(), 'name' => $request->name]);
        return redirect()->back()->with('success', 'Unit created.');
    }
    public function updateUnit(Request $request, $id) {
        $u = Unit::findOrFail($id);
        $request->validate(['name' => 'required']);
        $u->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Unit updated.');
    }
    public function destroyUnit($id) {
        Unit::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Unit deleted.');
    }

    // ITEMS
    public function indexItem(Request $request) {
        $query = Item::with([
            'category:id,name', 
            'type:id,name', 
            'manufacturer:id,name', 
            'unit:id,name'
        ]);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('code', 'LIKE', "%{$request->search}%")
                  ->orWhere('barcode', 'LIKE', "%{$request->search}%");
            });
        }

        $total_items = Item::count();
        $items_with_sufficient_stock = \App\Models\InventoryStock::selectRaw('item_id, SUM(current_stock - lock_stock) as available')
            ->groupBy('item_id')
            ->havingRaw('SUM(current_stock - lock_stock) >= 10')
            ->get()
            ->count();
        $low_stock_count = $total_items - $items_with_sufficient_stock;

        return view('master.items.index', [
            'data' => $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString(),
            'categories' => Category::select('id', 'name')->get(), 
            'types' => Type::select('id', 'name')->get(), 
            'manufacturers' => Manufacturer::select('id', 'name')->get(), 
            'units' => Unit::select('id', 'name')->get(),
            'search' => $request->search,
            'total_items' => $total_items,
            'low_stock_count' => $low_stock_count
        ]);
    }
    public function storeItem(Request $request) {
        $request->validate(['barcode' => 'required|unique:items', 'name' => 'required', 'category_id' => 'required', 'type_id' => 'required', 'manufacturer_id' => 'required', 'unit_id' => 'required']);
        $this->masterService->storeItem($request->all());
        return redirect()->back()->with('success', 'Item created.');
    }
    public function updateItem(Request $request, $id) {
        $request->validate(['barcode' => 'required|unique:items,barcode,'.$id, 'name' => 'required', 'category_id' => 'required', 'type_id' => 'required']);
        $this->masterService->updateItem($id, $request->all());
        return redirect()->back()->with('success', 'Item updated.');
    }
    public function destroyItem($id) {
        Item::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Item deleted.');
    }

    public function copySupplierToManufacturer($id) {
        $s = Supplier::findOrFail($id);
        
        // Check if manufacturer with same name already exists
        if (Manufacturer::where('name', $s->name)->exists()) {
            return redirect()->back()->with('error', 'Manufaktur dengan nama yang sama sudah terdaftar.');
        }

        $data = $s->toArray();
        unset($data['id'], $data['code'], $data['created_at'], $data['updated_at']);
        $data['code'] = Manufacturer::generateCode();
        
        Manufacturer::create($data);
        return redirect()->route('manufacturers.index')->with('success', 'Supplier berhasil di-copy ke Manufaktur.');
    }

    public function copyManufacturerToSupplier($id) {
        $m = Manufacturer::findOrFail($id);
        
        // Check if supplier with same name already exists
        if (Supplier::where('name', $m->name)->exists()) {
            return redirect()->back()->with('error', 'Supplier dengan nama yang sama sudah terdaftar.');
        }

        $data = $m->toArray();
        unset($data['id'], $data['code'], $data['created_at'], $data['updated_at']);
        $data['code'] = Supplier::generateCode();
        
        Supplier::create($data);
        return redirect()->route('suppliers.index')->with('success', 'Manufaktur berhasil di-copy ke Supplier.');
    }
}
