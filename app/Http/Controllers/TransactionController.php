<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockOpname;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // Inventory (Stock In/Out)
    public function indexInventory()
    {
        $data = StockTransaction::with(['item', 'warehouse', 'user'])->latest()->get();
        // Use join to get unit name efficiently
        $skuMasterList = \DB::table('items')
            ->leftJoin('units', 'items.unit_id', '=', 'units.id')
            ->select('items.id', 'items.code', 'items.name', 'units.name as unit_name')
            ->get();
        $warehouses = Warehouse::all();
        return view('transactions.inventory.index', compact('data', 'skuMasterList', 'warehouses'));
    }

    public function storeInventory(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required',
            'type' => 'required|in:IN,OUT',
            'reference_no' => 'required',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        foreach ($request->items as $item) {
            StockTransaction::create([
                'item_id' => $item['item_id'],
                'warehouse_id' => $request->warehouse_id,
                'type' => $request->type,
                'quantity' => $item['quantity'],
                'reference_no' => $request->reference_no,
                'note' => $item['note'] ?? $request->note,
                'user_id' => Auth::id(),
            ]);
        }

        return redirect()->back()->with('success', 'Transaksi multi-item berhasil disimpan');
    }

    // Stock Opname
    public function indexOpname()
    {
        $data = StockOpname::with(['item', 'warehouse', 'user', 'approver'])->latest()->get();
        return view('transactions.opname.index', compact('data'));
    }

    public function createOpname()
    {
        $items = Item::with('unit')->select('id', 'code', 'name', 'unit_id')->get();
        $warehouses = Warehouse::all();
        return view('transactions.opname.create', compact('items', 'warehouses'));
    }

    public function getStock(Request $request)
    {
        $item_id = $request->item_id;
        $warehouse_id = $request->warehouse_id;

        $stock = StockTransaction::where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->selectRaw("SUM(CASE WHEN type = 'IN' THEN quantity ELSE -quantity END) as total")
            ->value('total') ?? 0;

        return response()->json(['stock' => (float)$stock]);
    }

    public function storeOpname(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required',
            'items.*.physical_qty' => 'required|numeric|min:0',
        ]);

        foreach ($request->items as $item) {
            $system_qty = StockTransaction::where('item_id', $item['item_id'])
                ->where('warehouse_id', $request->warehouse_id)
                ->selectRaw("SUM(CASE WHEN type = 'IN' THEN quantity ELSE -quantity END) as total")
                ->value('total') ?? 0;

            StockOpname::create([
                'item_id' => $item['item_id'],
                'warehouse_id' => $request->warehouse_id,
                'system_qty' => $system_qty,
                'physical_qty' => $item['physical_qty'],
                'difference' => $item['physical_qty'] - $system_qty,
                'status' => 'PENDING',
                'note' => $item['note'] ?? null,
                'user_id' => Auth::id(),
            ]);
        }

        return redirect()->route('opname.index')->with('success', 'Permintaan opname berhasil diajukan dan menunggu persetujuan supervisor.');
    }

    public function indexOpnameApproval()
    {
        $data = StockOpname::with(['item.unit', 'warehouse', 'user'])
            ->where('status', 'PENDING')
            ->latest()
            ->get();
            
        return view('transactions.opname.approval', compact('data'));
    }

    public function approveOpname($id)
    {
        $opname = StockOpname::findOrFail($id);
        
        if ($opname->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Transaksi ini sudah diproses.');
        }

        $opname->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        // Create adjustment transaction if there is a difference
        if ($opname->difference != 0) {
            StockTransaction::create([
                'item_id' => $opname->item_id,
                'warehouse_id' => $opname->warehouse_id,
                'type' => $opname->difference > 0 ? 'IN' : 'OUT',
                'quantity' => abs($opname->difference),
                'reference_no' => 'ADJ-OPNAME-' . $opname->id,
                'note' => 'Penyesuaian Stock Opname: ' . $opname->note,
                'user_id' => Auth::id(),
            ]);
        }

        return redirect()->back()->with('success', 'Stock opname berhasil disetujui dan stok telah diperbarui.');
    }

    public function rejectOpname($id)
    {
        $opname = StockOpname::findOrFail($id);
        if ($opname->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Transaksi ini sudah diproses.');
        }

        $opname->update([
            'status' => 'REJECTED',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return redirect()->back()->with('success', 'Permintaan opname ditolak.');
    }

    // Stock Card
    public function indexStockCard(Request $request)
    {
        $search = $request->search;
        $item_id = $request->item_id;

        if ($item_id) {
            $item = Item::with('unit')->findOrFail($item_id);
            $transactions = StockTransaction::where('item_id', $item_id)
                ->with('warehouse')
                ->latest()
                ->get();
            
            // Calculate running balance (simple way for history)
            $current_stock = StockTransaction::where('item_id', $item_id)
                ->selectRaw("SUM(CASE WHEN type = 'IN' THEN quantity ELSE -quantity END) as total")
                ->value('total') ?? 0;

            return view('transactions.stock_card.detail', compact('item', 'transactions', 'current_stock'));
        }

        $items = Item::with(['unit', 'category'])
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->get()
            ->map(function($item) {
                $item->current_stock = StockTransaction::where('item_id', $item->id)
                    ->selectRaw("SUM(CASE WHEN type = 'IN' THEN quantity ELSE -quantity END) as total")
                    ->value('total') ?? 0;
                return $item;
            });

        return view('transactions.stock_card.index', compact('items', 'search'));
    }
}
