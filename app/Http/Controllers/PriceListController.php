<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PriceList;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class PriceListController extends Controller
{
    public function index(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $item_id = $request->item_id;

        $query = PriceList::with(['item.unit', 'warehouse']);

        if ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        }

        if ($item_id) {
            $query->where('item_id', $item_id);
        }

        $data = $query->latest()->get();
        $warehouses = Warehouse::all();
        $items = Item::select('id', 'code', 'name')->get();

        return view('master.price_lists.index', compact('data', 'warehouses', 'items', 'warehouse_id', 'item_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
            'warehouse_id' => 'required',
            'hna' => 'required|numeric',
            'hna_ppn' => 'required|numeric',
            'het' => 'required|numeric',
            'start_date' => 'required|date',
        ]);

        PriceList::create($request->all());

        return redirect()->back()->with('success', 'Harga berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $price = PriceList::findOrFail($id);
        
        $request->validate([
            'item_id' => 'required',
            'warehouse_id' => 'required',
            'hna' => 'required|numeric',
            'hna_ppn' => 'required|numeric',
            'het' => 'required|numeric',
            'start_date' => 'required|date',
        ]);

        $price->update($request->all());

        return redirect()->back()->with('success', 'Harga berhasil diperbarui');
    }

    public function destroy($id)
    {
        $price = PriceList::findOrFail($id);
        $price->delete();

        return redirect()->back()->with('success', 'Harga berhasil dihapus');
    }

    public function getPrice(Request $request)
    {
        $price = PriceList::where('item_id', $request->item_id)
            ->where('is_active', true)
            ->latest()
            ->first();

        return response()->json([
            'hna' => $price ? $price->hna : 0,
            'hna_ppn' => $price ? $price->hna_ppn : 0,
            'het' => $price ? $price->het : 0,
        ]);
    }
}
