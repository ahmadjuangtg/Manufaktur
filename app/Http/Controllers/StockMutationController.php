<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockMutation;
use App\Models\StockMutationDetail;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockMutationController extends Controller
{
    // 1. Request Menu
    public function indexRequest()
    {
        $data = StockMutation::with(['fromWarehouse', 'toWarehouse', 'user'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
        
        $warehouses = Warehouse::all();
        $items = Item::with('unit')->get();
        
        return view('transactions.mutations.request', compact('data', 'warehouses', 'items'));
    }

    public function storeRequest(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required',
            'to_warehouse_id' => 'required|different:from_warehouse_id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request) {
            $mutation = StockMutation::create([
                'reference_no' => 'MUT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'status' => 'PENDING',
                'note' => $request->note,
                'user_id' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                StockMutationDetail::create([
                    'stock_mutation_id' => $mutation->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Permintaan mutasi berhasil dibuat.');
    }

    // 2. Approval Menu
    public function indexApproval()
    {
        $query = StockMutation::with(['fromWarehouse', 'toWarehouse', 'user'])
            ->where('status', 'PENDING');

        // If user is not Super Admin (doesn't have 'all' permission), filter by warehouse access
        if (!Auth::user()->hasPermission('all')) {
            $userWarehouseIds = Auth::user()->warehouses->pluck('id');
            $query->whereIn('from_warehouse_id', $userWarehouseIds);
        }
        
        $data = $query->latest()->get();

        return view('transactions.mutations.approval', compact('data'));
    }

    public function approve($id)
    {
        $mutation = StockMutation::findOrFail($id);
        $mutation->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Permintaan mutasi disetujui.');
    }

    public function reject($id)
    {
        $mutation = StockMutation::findOrFail($id);
        $mutation->update([
            'status' => 'REJECTED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Permintaan mutasi ditolak.');
    }

    // 3. Execution/History Menu
    public function indexMutation()
    {
        $data = StockMutation::with(['fromWarehouse', 'toWarehouse', 'user', 'details.item'])
            ->latest()
            ->get();
        
        return view('transactions.mutations.index', compact('data'));
    }

    public function send($id)
    {
        $mutation = StockMutation::with('details')->findOrFail($id);
        
        if ($mutation->status !== 'APPROVED') {
            return redirect()->back()->with('error', 'Hanya mutasi yang disetujui yang bisa dikirim.');
        }

        DB::transaction(function () use ($mutation) {
            $mutation->update([
                'status' => 'SENDING',
                'sent_by' => Auth::id(),
                'sent_at' => now(),
            ]);

            // Create Stock OUT from Source (from_warehouse_id)
            foreach ($mutation->details as $detail) {
                StockTransaction::create([
                    'item_id' => $detail->item_id,
                    'warehouse_id' => $mutation->from_warehouse_id,
                    'type' => 'OUT',
                    'quantity' => $detail->quantity,
                    'reference_no' => $mutation->reference_no,
                    'note' => 'Mutasi Keluar ke ' . $mutation->toWarehouse->name,
                    'user_id' => Auth::id(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Barang berhasil dikirim (Stok gudang asal berkurang).');
    }

    public function receive($id)
    {
        $mutation = StockMutation::with('details')->findOrFail($id);
        
        if ($mutation->status !== 'SENDING') {
            return redirect()->back()->with('error', 'Hanya mutasi berstatus SENDING yang bisa diterima.');
        }

        DB::transaction(function () use ($mutation) {
            $mutation->update([
                'status' => 'COMPLETED',
                'received_by' => Auth::id(),
                'received_at' => now(),
            ]);

            // Create Stock IN to Destination (to_warehouse_id)
            foreach ($mutation->details as $detail) {
                StockTransaction::create([
                    'item_id' => $detail->item_id,
                    'warehouse_id' => $mutation->to_warehouse_id,
                    'type' => 'IN',
                    'quantity' => $detail->quantity,
                    'reference_no' => $mutation->reference_no,
                    'note' => 'Mutasi Masuk dari ' . $mutation->fromWarehouse->name,
                    'user_id' => Auth::id(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Barang berhasil diterima (Stok gudang tujuan bertambah).');
    }

    public function show($id)
    {
        $mutation = StockMutation::with(['fromWarehouse', 'toWarehouse', 'user', 'details.item.unit', 'approver', 'sender', 'receiver'])->findOrFail($id);
        return response()->json($mutation);
    }
}
