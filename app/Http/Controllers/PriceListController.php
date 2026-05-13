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
        $search = $request->search;

        $query = PriceList::with(['item.unit', 'warehouse'])
            ->join('items', 'items.id', '=', 'price_lists.item_id')
            ->join('warehouses', 'warehouses.id', '=', 'price_lists.warehouse_id')
            ->select('price_lists.*');

        if ($warehouse_id) {
            $query->where('price_lists.warehouse_id', $warehouse_id);
        }

        if ($item_id) {
            $query->where('price_lists.item_id', $item_id);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('items.name', 'LIKE', "%{$search}%")
                  ->orWhere('items.code', 'LIKE', "%{$search}%");
            });
        }

        // We paginate the raw data, but since grouping is needed, 
        // we use a larger limit to ensure most groups stay together, 
        // OR better: we fetch more and group.
        // For simplicity and correctness with grouping badges, we'll fetch and paginate the collection.
        $dataRaw = $query->orderBy('price_lists.updated_at', 'desc')
            ->orderBy('items.code', 'asc')
            ->get();

        $dataGrouped = $dataRaw->groupBy(function($item) {
            return $item->item_id . '-' . $item->hna . '-' . $item->hna_ppn . '-' . $item->het . '-' . $item->start_date;
        })->map(function($group) {
            $first = $group->first();
            $first->all_warehouses = $group->map(function($item) {
                return $item->warehouse;
            });
            $first->all_ids = $group->pluck('id')->toArray();
            return $first;
        })->values();

        // Paginate the collection
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 20;
        $currentPageItems = $dataGrouped->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $data = new \Illuminate\Pagination\LengthAwarePaginator($currentPageItems, count($dataGrouped), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        $warehouses = Warehouse::all();
        $items = Item::select('id', 'code', 'name')->get();
        $existingItemIds = PriceList::pluck('item_id')->unique()->toArray();

        return view('master.price_lists.index', compact('data', 'warehouses', 'items', 'existingItemIds', 'warehouse_id', 'item_id', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
            'warehouse_id' => 'required|array|min:1',
            'hna' => 'required|numeric',
            'hna_ppn' => 'required|numeric',
            'het' => 'required|numeric',
            'start_date' => 'required|date',
        ]);

        $warehouses = $request->warehouse_id;
        
        foreach ($warehouses as $w_id) {
            PriceList::updateOrCreate(
                ['item_id' => $request->item_id, 'warehouse_id' => $w_id],
                [
                    'hna' => $request->hna,
                    'hna_ppn' => $request->hna_ppn,
                    'het' => $request->het,
                    'start_date' => $request->start_date,
                ]
            );
        }

        return redirect()->back()->with('success', 'Harga berhasil disimpan untuk ' . count($warehouses) . ' gudang.');
    }

    public function update(Request $request, $id)
    {
        // For update, we use the same logic as store to support adding more warehouses
        return $this->store($request);
    }

    public function destroy($id)
    {
        $price = PriceList::findOrFail($id);
        $price->delete();

        return redirect()->back()->with('success', 'Harga berhasil dihapus');
    }

    public function checkItemWarehouses(Request $request)
    {
        $warehouseIds = PriceList::where('item_id', $request->item_id)
            ->pluck('warehouse_id')
            ->toArray();
            
        return response()->json($warehouseIds);
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
