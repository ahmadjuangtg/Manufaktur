<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryStock;
use App\Models\StockOpname;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // Inventory (Stock In/Out)
    // Inventory (Stock In/Out)
    public function indexInventory()
    {
        // Use pagination instead of get() for large datasets
        $data = StockTransaction::with(['item', 'warehouse', 'user'])
            ->latest()
            ->paginate(50)
            ->withQueryString();

        // Efficiently fetch master list for dropdowns
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

        try {
            \DB::transaction(function() use ($request) {
                foreach ($request->items as $item) {
                    if ($request->type === 'OUT') {
                        $inventory = InventoryStock::where('item_id', $item['item_id'])
                            ->where('warehouse_id', $request->warehouse_id)
                            ->first();
                        
                        $available = $inventory ? $inventory->available_stock : 0;
                        if ($available < $item['quantity']) {
                            $itemName = Item::find($item['item_id'])->name ?? $item['item_id'];
                            throw new \Exception("Stok Tersedia (Available Stock) tidak mencukupi untuk {$itemName}. Tersedia: {$available}, Diminta: {$item['quantity']}");
                        }
                    }

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
            });

            return redirect()->back()->with('success', 'Transaksi multi-item berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Stock Opname
    public function indexOpname(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $status = $request->status;
        $sort_by = $request->sort_by ?? 'created_at';
        $sort_order = $request->sort_order ?? 'desc';

        $user_warehouses = Auth::user()->warehouses->pluck('id')->toArray();
        $is_superadmin = (Auth::user()->role->name ?? '') === 'Super Administrator';

        $data = StockOpname::with(['item', 'warehouse', 'user', 'approver'])
            ->when(!$is_superadmin && count($user_warehouses) > 0, function($q) use ($user_warehouses) {
                $q->whereIn('warehouse_id', $user_warehouses);
            })
            ->when(!$is_superadmin && count($user_warehouses) === 0, function($q) {
                $q->where('warehouse_id', -1);
            })
            ->when($warehouse_id, function($q) use ($warehouse_id) {
                $q->where('warehouse_id', $warehouse_id);
            })
            ->when($status, function($q) use ($status) {
                $q->where('status', $status);
            });

        // Handle sorting
        if ($sort_by === 'warehouse') {
            $data->join('warehouses', 'stock_opnames.warehouse_id', '=', 'warehouses.id')
                 ->orderBy('warehouses.name', $sort_order)
                 ->select('stock_opnames.*');
        } else {
            $data->orderBy('stock_opnames.' . $sort_by, $sort_order);
        }

        $data = $data->paginate(20)->withQueryString();
        $warehouses = $is_superadmin ? Warehouse::all() : Auth::user()->warehouses;
        
        return view('transactions.opname.index', compact('data', 'warehouses'));
    }

    public function createOpname()
    {
        $items = Item::with('unit')->select('id', 'code', 'name', 'unit_id')->get();
        $is_superadmin = (Auth::user()->role->name ?? '') === 'Super Administrator';
        $warehouses = $is_superadmin ? Warehouse::all() : Auth::user()->warehouses;
        return view('transactions.opname.create', compact('items', 'warehouses'));
    }

    public function getStock(Request $request)
    {
        $item_id = $request->item_id;
        $warehouse_id = $request->warehouse_id;

        $stock = InventoryStock::where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->value('current_stock') ?? 0;

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

        \DB::transaction(function() use ($request) {
            foreach ($request->items as $item) {
                $system_qty = InventoryStock::where('item_id', $item['item_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->value('current_stock') ?? 0;

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
        });

        return redirect()->route('opname.index')->with('success', 'Permintaan opname berhasil diajukan.');
    }

    public function indexOpnameApproval(Request $request)
    {
        $warehouse_id = $request->warehouse_id;

        $user_warehouses = Auth::user()->warehouses->pluck('id')->toArray();
        $is_superadmin = (Auth::user()->role->name ?? '') === 'Super Administrator';

        $data = StockOpname::with(['item.unit', 'warehouse', 'user'])
            ->where('status', 'PENDING')
            ->when(!$is_superadmin && count($user_warehouses) > 0, function($q) use ($user_warehouses) {
                $q->whereIn('warehouse_id', $user_warehouses);
            })
            ->when(!$is_superadmin && count($user_warehouses) === 0, function($q) {
                $q->where('warehouse_id', -1);
            })
            ->when($warehouse_id, function($q) use ($warehouse_id) {
                $q->where('warehouse_id', $warehouse_id);
            })
            ->latest()
            ->get();
            
        $warehouses = $is_superadmin ? Warehouse::all() : Auth::user()->warehouses;
        return view('transactions.opname.approval', compact('data', 'warehouses'));
    }

    public function approveOpname($id)
    {
        $opname = StockOpname::findOrFail($id);
        
        if ($opname->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Transaksi ini sudah diproses.');
        }

        \DB::transaction(function() use ($opname) {
            $opname->update([
                'status' => 'APPROVED',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

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
        });

        return redirect()->back()->with('success', 'Stock opname disetujui.');
    }

    public function rejectOpname(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5'
        ]);

        $opname = StockOpname::findOrFail($id);
        if ($opname->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Transaksi ini sudah diproses.');
        }

        $opname->update([
            'status' => 'REJECTED',
            'rejection_reason' => $request->rejection_reason,
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
                ->paginate(50)
                ->withQueryString();
            
            $stock_data = InventoryStock::where('item_id', $item_id)
                ->selectRaw('SUM(current_stock) as current_stock, SUM(lock_stock) as lock_stock, SUM(shadow_stock) as shadow_stock')
                ->first();
                
            $current_stock = $stock_data->current_stock ?? 0;
            $lock_stock = $stock_data->lock_stock ?? 0;
            $shadow_stock = $stock_data->shadow_stock ?? 0;
            $available_stock = max(0, $current_stock - $lock_stock);

            $warehouse_stock = InventoryStock::where('item_id', $item_id)
                ->join('warehouses', 'inventory_stocks.warehouse_id', '=', 'warehouses.id')
                ->select('warehouses.name', 'inventory_stocks.current_stock as total', 'inventory_stocks.lock_stock', 'inventory_stocks.shadow_stock')
                ->get();

            return view('transactions.stock_card.detail', compact('item', 'transactions', 'current_stock', 'lock_stock', 'shadow_stock', 'available_stock', 'warehouse_stock'));
        }

        // Optimized to join inventory_stocks
        $items = Item::with(['unit', 'category'])
            ->leftJoin('inventory_stocks', 'items.id', '=', 'inventory_stocks.item_id')
            ->select('items.*')
            ->selectRaw("SUM(inventory_stocks.current_stock) as current_stock")
            ->selectRaw("SUM(inventory_stocks.lock_stock) as lock_stock")
            ->selectRaw("SUM(inventory_stocks.shadow_stock) as shadow_stock")
            ->when($search, function($q) use ($search) {
                $q->where('items.name', 'like', "%{$search}%")
                  ->orWhere('items.code', 'like', "%{$search}%");
            })
            ->groupBy('items.id')
            ->paginate(20)
            ->withQueryString();

        return view('transactions.stock_card.index', compact('items', 'search'));
    }

    public function printStockCard($id)
    {
        $item = Item::with('unit')->findOrFail($id);
        
        $transactions = StockTransaction::where('item_id', $id)
            ->with('warehouse')
            ->latest()
            ->get(); // Fetch all without pagination for print
        
        $stock_data = InventoryStock::where('item_id', $id)
            ->selectRaw('SUM(current_stock) as current_stock, SUM(lock_stock) as lock_stock, SUM(shadow_stock) as shadow_stock')
            ->first();
            
        $current_stock = $stock_data->current_stock ?? 0;
        $lock_stock = $stock_data->lock_stock ?? 0;
        $shadow_stock = $stock_data->shadow_stock ?? 0;
        $available_stock = max(0, $current_stock - $lock_stock);

        return view('transactions.stock_card.print', compact('item', 'transactions', 'current_stock', 'lock_stock', 'shadow_stock', 'available_stock'));
    }
}
