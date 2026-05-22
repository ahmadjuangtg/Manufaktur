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
            ->paginate(10)
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

        $data = $data->paginate(10)->withQueryString();
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
            $warehouse_id = $request->warehouse_id;

            // Filter transactions by warehouse if selected
            $transactions_query = StockTransaction::where('item_id', $item_id)->with('warehouse');
            if ($warehouse_id) {
                $transactions_query->where('warehouse_id', $warehouse_id);
            }
            $transactions = $transactions_query->latest()->paginate(10)->withQueryString();
            
            // Calculate summary stock
            $stock_query = InventoryStock::where('item_id', $item_id);
            if ($warehouse_id) {
                $stock_query->where('warehouse_id', $warehouse_id);
            }
            $stock_data = $stock_query
                ->selectRaw('SUM(current_stock) as current_stock, SUM(lock_stock) as lock_stock, SUM(shadow_stock) as shadow_stock')
                ->first();
                
            $current_stock = $stock_data->current_stock ?? 0;
            $lock_stock = $stock_data->lock_stock ?? 0;
            $shadow_stock = $stock_data->shadow_stock ?? 0;
            $available_stock = max(0, $current_stock - $lock_stock);

            // Get total warehouses count
            $warehouse_count = Warehouse::count();

            // Stock per warehouse grid always shows all warehouses
            $warehouse_stock = InventoryStock::where('item_id', $item_id)
                ->join('warehouses', 'inventory_stocks.warehouse_id', '=', 'warehouses.id')
                ->select('warehouses.id as warehouse_id', 'warehouses.name', 'inventory_stocks.current_stock as total', 'inventory_stocks.lock_stock', 'inventory_stocks.shadow_stock')
                ->get();

            // Get permitted warehouses for the dropdown
            $is_superadmin = (Auth::user()->role->name ?? '') === 'Super Administrator';
            $warehouses = $is_superadmin ? Warehouse::all() : Auth::user()->warehouses;

            // Calculate starting balance based on filtered transactions
            $starting_balance = 0;
            if ($transactions->isNotEmpty()) {
                $oldest = $transactions->last();
                $starting_balance_query = StockTransaction::where('item_id', $item_id);
                if ($warehouse_id) {
                    $starting_balance_query->where('warehouse_id', $warehouse_id);
                }
                $starting_balance = $starting_balance_query
                    ->where(function($query) use ($oldest) {
                        $query->where('created_at', '<', $oldest->created_at)
                              ->orWhere(function($q) use ($oldest) {
                                  $q->where('created_at', '=', $oldest->created_at)
                                    ->where('id', '<', $oldest->id);
                              });
                    })
                    ->selectRaw("SUM(CASE WHEN type = 'IN' THEN quantity WHEN type = 'OUT' THEN -quantity ELSE 0 END) as balance")
                    ->value('balance') ?? 0;
            }

            return view('transactions.stock_card.detail', compact(
                'item', 'transactions', 'current_stock', 'lock_stock', 'shadow_stock', 'available_stock', 
                'warehouse_stock', 'starting_balance', 'warehouses', 'warehouse_id', 'warehouse_count'
            ));
        }

        $warehouse_id = $request->warehouse_id;

        $subquery_current = \DB::table('inventory_stocks')
            ->selectRaw('COALESCE(SUM(current_stock), 0)')
            ->whereColumn('inventory_stocks.item_id', 'items.id');

        $subquery_lock = \DB::table('inventory_stocks')
            ->selectRaw('COALESCE(SUM(lock_stock), 0)')
            ->whereColumn('inventory_stocks.item_id', 'items.id');

        $subquery_shadow = \DB::table('inventory_stocks')
            ->selectRaw('COALESCE(SUM(shadow_stock), 0)')
            ->whereColumn('inventory_stocks.item_id', 'items.id');

        if ($warehouse_id) {
            $subquery_current->where('inventory_stocks.warehouse_id', $warehouse_id);
            $subquery_lock->where('inventory_stocks.warehouse_id', $warehouse_id);
            $subquery_shadow->where('inventory_stocks.warehouse_id', $warehouse_id);
        }

        $items = Item::with(['unit', 'category'])
            ->select('items.*')
            ->selectSub($subquery_current, 'current_stock')
            ->selectSub($subquery_lock, 'lock_stock')
            ->selectSub($subquery_shadow, 'shadow_stock')
            ->when($search, function($q) use ($search) {
                $q->where(function($inner) use ($search) {
                    $inner->where('items.name', 'like', "%{$search}%")
                          ->orWhere('items.code', 'like', "%{$search}%");
                });
            })
            ->paginate(10)
            ->withQueryString();

        $total_items = Item::count();

        // Calculate dynamic low stock count based on selected warehouse
        $items_with_sufficient_stock_query = InventoryStock::selectRaw('item_id, SUM(current_stock - lock_stock) as available');
        if ($warehouse_id) {
            $items_with_sufficient_stock_query->where('warehouse_id', $warehouse_id);
        }
        $items_with_sufficient_stock = $items_with_sufficient_stock_query
            ->groupBy('item_id')
            ->havingRaw('SUM(current_stock - lock_stock) >= 10')
            ->get()
            ->count();
        $low_stock_count = $total_items - $items_with_sufficient_stock;

        // Get permitted warehouses for the dropdown
        $is_superadmin = (Auth::user()->role->name ?? '') === 'Super Administrator';
        $warehouses = $is_superadmin ? Warehouse::all() : Auth::user()->warehouses;

        return view('transactions.stock_card.index', compact('items', 'search', 'total_items', 'low_stock_count', 'warehouses', 'warehouse_id'));
    }

    public function printStockCard(Request $request, $id)
    {
        $item = Item::with('unit')->findOrFail($id);
        $warehouse_id = $request->warehouse_id;

        $transactions_query = StockTransaction::where('item_id', $id)->with('warehouse');
        if ($warehouse_id) {
            $transactions_query->where('warehouse_id', $warehouse_id);
        }
        $transactions = $transactions_query->latest()->get(); // Fetch all without pagination for print

        $stock_query = InventoryStock::where('item_id', $id);
        if ($warehouse_id) {
            $stock_query->where('warehouse_id', $warehouse_id);
        }
        $stock_data = $stock_query
            ->selectRaw('SUM(current_stock) as current_stock, SUM(lock_stock) as lock_stock, SUM(shadow_stock) as shadow_stock')
            ->first();
            
        $current_stock = $stock_data->current_stock ?? 0;
        $lock_stock = $stock_data->lock_stock ?? 0;
        $shadow_stock = $stock_data->shadow_stock ?? 0;
        $available_stock = max(0, $current_stock - $lock_stock);

        $warehouse_name = 'Semua Gudang';
        if ($warehouse_id) {
            $warehouse = Warehouse::find($warehouse_id);
            $warehouse_name = $warehouse ? $warehouse->name : 'Semua Gudang';
        }

        return view('transactions.stock_card.print', compact('item', 'transactions', 'current_stock', 'lock_stock', 'shadow_stock', 'available_stock', 'warehouse_name'));
    }

    public function exportExcelSingleStockCard(Request $request, $id)
    {
        $item = Item::with(['unit', 'category'])->findOrFail($id);
        $warehouse_id = $request->warehouse_id;

        $transactions_query = StockTransaction::where('item_id', $id)->with('warehouse');
        if ($warehouse_id) {
            $transactions_query->where('warehouse_id', $warehouse_id);
        }
        $transactions = $transactions_query->latest()->get();

        $stock_query = InventoryStock::where('item_id', $id);
        if ($warehouse_id) {
            $stock_query->where('warehouse_id', $warehouse_id);
        }
        $stock_data = $stock_query
            ->selectRaw('SUM(current_stock) as current_stock, SUM(lock_stock) as lock_stock, SUM(shadow_stock) as shadow_stock')
            ->first();
            
        $current_stock = $stock_data->current_stock ?? 0;
        $lock_stock = $stock_data->lock_stock ?? 0;
        $shadow_stock = $stock_data->shadow_stock ?? 0;
        $available_stock = max(0, $current_stock - $lock_stock);

        $warehouse_name = 'Semua Gudang';
        if ($warehouse_id) {
            $warehouse = Warehouse::find($warehouse_id);
            $warehouse_name = $warehouse ? $warehouse->name : 'Semua Gudang';
        }

        $filename = 'Laporan_Stock_Card_' . str_replace(' ', '_', $item->name) . '_' . str_replace(' ', '_', $warehouse_name) . '_' . date('Ymd_His') . '.xls';
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        return view('transactions.stock_card.export_single', compact(
            'item', 'transactions', 'current_stock', 'lock_stock', 'shadow_stock', 'available_stock', 'warehouse_name'
        ));
    }

    public function printAllStockCards(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $search = $request->search;

        $warehouse = null;
        if ($warehouse_id) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        $subquery_current = \DB::table('inventory_stocks')
            ->selectRaw('COALESCE(SUM(current_stock), 0)')
            ->whereColumn('inventory_stocks.item_id', 'items.id');

        $subquery_lock = \DB::table('inventory_stocks')
            ->selectRaw('COALESCE(SUM(lock_stock), 0)')
            ->whereColumn('inventory_stocks.item_id', 'items.id');

        $subquery_shadow = \DB::table('inventory_stocks')
            ->selectRaw('COALESCE(SUM(shadow_stock), 0)')
            ->whereColumn('inventory_stocks.item_id', 'items.id');

        if ($warehouse_id) {
            $subquery_current->where('inventory_stocks.warehouse_id', $warehouse_id);
            $subquery_lock->where('inventory_stocks.warehouse_id', $warehouse_id);
            $subquery_shadow->where('inventory_stocks.warehouse_id', $warehouse_id);
        }

        $items = Item::with(['unit', 'category'])
            ->select('items.*')
            ->selectSub($subquery_current, 'current_stock')
            ->selectSub($subquery_lock, 'lock_stock')
            ->selectSub($subquery_shadow, 'shadow_stock')
            ->when($search, function($q) use ($search) {
                $q->where(function($inner) use ($search) {
                    $inner->where('items.name', 'like', "%{$search}%")
                          ->orWhere('items.code', 'like', "%{$search}%");
                });
            })
            ->get();

        $total_current = 0;
        $total_lock = 0;
        $total_shadow = 0;
        $total_available = 0;

        foreach ($items as $item) {
            $total_current += $item->current_stock;
            $total_lock += $item->lock_stock;
            $total_shadow += $item->shadow_stock;
            $total_available += max(0, $item->current_stock - $item->lock_stock);
        }

        $warehouse_name = $warehouse ? $warehouse->name : 'Semua Gudang';

        return view('transactions.stock_card.print_all', compact(
            'items', 'warehouse_name', 'total_current', 'total_lock', 'total_shadow', 'total_available'
        ));
    }

    public function exportExcelStockCard(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $search = $request->search;

        $warehouse = null;
        if ($warehouse_id) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        $subquery_current = \DB::table('inventory_stocks')
            ->selectRaw('COALESCE(SUM(current_stock), 0)')
            ->whereColumn('inventory_stocks.item_id', 'items.id');

        $subquery_lock = \DB::table('inventory_stocks')
            ->selectRaw('COALESCE(SUM(lock_stock), 0)')
            ->whereColumn('inventory_stocks.item_id', 'items.id');

        $subquery_shadow = \DB::table('inventory_stocks')
            ->selectRaw('COALESCE(SUM(shadow_stock), 0)')
            ->whereColumn('inventory_stocks.item_id', 'items.id');

        if ($warehouse_id) {
            $subquery_current->where('inventory_stocks.warehouse_id', $warehouse_id);
            $subquery_lock->where('inventory_stocks.warehouse_id', $warehouse_id);
            $subquery_shadow->where('inventory_stocks.warehouse_id', $warehouse_id);
        }

        $items = Item::with(['unit', 'category'])
            ->select('items.*')
            ->selectSub($subquery_current, 'current_stock')
            ->selectSub($subquery_lock, 'lock_stock')
            ->selectSub($subquery_shadow, 'shadow_stock')
            ->when($search, function($q) use ($search) {
                $q->where(function($inner) use ($search) {
                    $inner->where('items.name', 'like', "%{$search}%")
                          ->orWhere('items.code', 'like', "%{$search}%");
                });
            })
            ->get();

        $total_current = 0;
        $total_lock = 0;
        $total_shadow = 0;
        $total_available = 0;

        foreach ($items as $item) {
            $total_current += $item->current_stock;
            $total_lock += $item->lock_stock;
            $total_shadow += $item->shadow_stock;
            $total_available += max(0, $item->current_stock - $item->lock_stock);
        }

        $warehouse_name = $warehouse ? $warehouse->name : 'Semua Gudang';

        $filename = 'Laporan_Stock_Card_' . str_replace(' ', '_', $warehouse_name) . '_' . date('Ymd_His') . '.xls';
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        return view('transactions.stock_card.export_all', compact(
            'items', 'warehouse_name', 'total_current', 'total_lock', 'total_shadow', 'total_available'
        ));
    }
}
