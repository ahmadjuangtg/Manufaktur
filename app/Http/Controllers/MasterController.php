<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Type;
use App\Models\Manufacturer;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;

class MasterController extends Controller {
    // SUPPLIERS
    public function indexSupplier() { 
        return view('master.suppliers.index', [
            'data' => Supplier::with('items')->get(),
            'items' => Item::all()
        ]); 
    }
    public function storeSupplier(Request $request) {
        $request->validate(['name' => 'required']);
        $data = $request->all();
        $data['code'] = Supplier::generateCode();
        $s = Supplier::create($data);
        if ($request->has('item_ids')) {
            $s->items()->sync($request->item_ids);
        }
        return redirect()->back()->with('success', 'Supplier created.');
    }
    public function updateSupplier(Request $request, $id) {
        $s = Supplier::findOrFail($id);
        $request->validate(['name' => 'required']);
        $s->update($request->all());
        if ($request->has('item_ids')) {
            $s->items()->sync($request->item_ids);
        } else {
            $s->items()->detach();
        }
        return redirect()->back()->with('success', 'Supplier updated.');
    }
    public function destroySupplier($id) {
        Supplier::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Supplier deleted.');
    }

    // CATEGORIES
    public function indexCategory() { return view('master.categories.index', ['data' => Category::all()]); }
    public function storeCategory(Request $request) {
        $request->validate(['prefix' => 'required|unique:categories', 'name' => 'required']);
        Category::create(['code' => Category::generateCode(), 'prefix' => strtoupper($request->prefix), 'name' => $request->name]);
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
    public function indexType() { return view('master.types.index', ['data' => Type::all()]); }
    public function storeType(Request $request) {
        $request->validate(['prefix' => 'required|unique:types', 'name' => 'required']);
        Type::create(['code' => Type::generateCode(), 'prefix' => strtoupper($request->prefix), 'name' => $request->name]);
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
    public function indexManufacturer() { return view('master.manufacturers.index', ['data' => Manufacturer::all()]); }
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
    public function indexUnit() { return view('master.units.index', ['data' => Unit::all()]); }
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
    public function indexItem() {
        return view('master.items.index', [
            'data' => Item::with(['category', 'type', 'manufacturer', 'unit'])->get(),
            'categories' => Category::all(), 'types' => Type::all(), 'manufacturers' => Manufacturer::all(), 'units' => Unit::all()
        ]);
    }
    public function storeItem(Request $request) {
        $request->validate(['barcode' => 'required|unique:items', 'name' => 'required', 'category_id' => 'required', 'type_id' => 'required', 'manufacturer_id' => 'required', 'unit_id' => 'required']);
        $data = $request->all();
        $data['code'] = Item::generateCode($request->category_id, $request->type_id);
        $data['display_name'] = $request->name;
        Item::create($data);
        return redirect()->back()->with('success', 'Item created.');
    }
    public function updateItem(Request $request, $id) {
        $item = Item::findOrFail($id);
        $request->validate(['barcode' => 'required|unique:items,barcode,'.$id, 'name' => 'required', 'category_id' => 'required', 'type_id' => 'required']);
        $data = $request->all();
        if ($item->category_id != $request->category_id || $item->type_id != $request->type_id) {
            $data['code'] = Item::generateCode($request->category_id, $request->type_id);
        }
        $data['display_name'] = $request->name;
        $item->update($data);
        return redirect()->back()->with('success', 'Item updated.');
    }
    public function destroyItem($id) {
        Item::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Item deleted.');
    }
}
