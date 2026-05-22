<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockMutation;
use App\Models\Warehouse;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockMutationController extends Controller
{
    protected $inventoryService;

    public function __construct(\App\Services\InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    // 1. Request Menu
    public function indexRequest(Request $request)
    {
        $from_warehouse_id = $request->from_warehouse_id;
        $to_warehouse_id = $request->to_warehouse_id;
        $status = $request->status;

        $data = StockMutation::with(['fromWarehouse', 'toWarehouse', 'user', 'workOrder'])
            ->where('user_id', Auth::id())
            ->when($from_warehouse_id, function($q) use ($from_warehouse_id) {
                $q->where('from_warehouse_id', $from_warehouse_id);
            })
            ->when($to_warehouse_id, function($q) use ($to_warehouse_id) {
                $q->where('to_warehouse_id', $to_warehouse_id);
            })
            ->when($status, function($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest()
            ->get();
        
        $is_superadmin = (Auth::user()->role->name ?? '') === 'Super Administrator';
        $warehouses = $is_superadmin ? Warehouse::all() : Auth::user()->warehouses;
        $allWarehouses = Warehouse::all();
        $items = Item::with('unit')->get();
        $workOrders = WorkOrder::whereIn('status', ['pending', 'ready_to_production', 'in_progress'])
            ->latest()
            ->get();

        $selected_wo_id = request('work_order_id');
        
        return view('transactions.mutations.request', compact('data', 'warehouses', 'allWarehouses', 'items', 'workOrders', 'selected_wo_id'));
    }

    public function storeRequest(Request $request)
    {
        $request->validate([
            'work_order_id' => 'nullable|exists:work_orders,id',
            'from_warehouse_id' => 'required',
            'to_warehouse_id' => 'required|different:from_warehouse_id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            $this->inventoryService->createMutationRequest($request->all());
            return redirect()->back()->with('success', 'Permintaan mutasi berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // 2. Approval Menu
    public function indexApproval(Request $request)
    {
        $from_warehouse_id = $request->from_warehouse_id;
        $to_warehouse_id = $request->to_warehouse_id;

        $query = StockMutation::with(['fromWarehouse', 'toWarehouse', 'user', 'workOrder'])
            ->where('status', 'PENDING')
            ->when($from_warehouse_id, function($q) use ($from_warehouse_id) {
                $q->where('from_warehouse_id', $from_warehouse_id);
            })
            ->when($to_warehouse_id, function($q) use ($to_warehouse_id) {
                $q->where('to_warehouse_id', $to_warehouse_id);
            });

        if (!Auth::user()->hasPermission('all')) {
            $userWarehouseIds = Auth::user()->warehouses->pluck('id');
            $query->whereIn('from_warehouse_id', $userWarehouseIds);
        }
        
        $data = $query->latest()->get();
        $is_superadmin = (Auth::user()->role->name ?? '') === 'Super Administrator';
        $warehouses = $is_superadmin ? Warehouse::all() : Auth::user()->warehouses;
        $allWarehouses = Warehouse::all();
        
        return view('transactions.mutations.approval', compact('data', 'warehouses', 'allWarehouses'));
    }

    public function approve($id)
    {
        try {
            $this->inventoryService->approveMutation($id);
            return redirect()->back()->with('success', 'Permintaan mutasi disetujui dan stok telah di-booking (Lock).');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $mutation = StockMutation::findOrFail($id);
        $mutation->update([
            'status' => 'REJECTED',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Permintaan mutasi ditolak.');
    }

    public function send($id)
    {
        $mutation = StockMutation::findOrFail($id);
        $mutation->update([
            'status' => 'SENDING',
            'sent_by' => Auth::id(),
            'sent_at' => now(),
        ]);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Barang sedang dikirim.']);
        }
        return redirect()->back()->with('success', 'Barang sedang dikirim.');
    }

    public function receive($id)
    {
        try {
            $mutation = StockMutation::findOrFail($id);
            $mutation->update([
                'received_by' => Auth::id(),
                'received_at' => now(),
            ]);
            $this->inventoryService->completeMutation($id);

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Mutasi stok berhasil diselesaikan.']);
            }
            return redirect()->back()->with('success', 'Mutasi stok berhasil diselesaikan dan stok telah berpindah.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // 3. Mutation History (Gudang)
    public function indexMutation(Request $request)
    {
        $from_warehouse_id = $request->from_warehouse_id;
        $to_warehouse_id = $request->to_warehouse_id;
        $status = $request->status;

        $data = StockMutation::with([
            'fromWarehouse', 
            'toWarehouse', 
            'user', 
            'workOrder', 
            'details.item.unit', 
            'deliveries.item.unit', 
            'deliveries.sender', 
            'deliveries.receiver'
        ])
            ->when($from_warehouse_id, function($q) use ($from_warehouse_id) {
                $q->where('from_warehouse_id', $from_warehouse_id);
            })
            ->when($to_warehouse_id, function($q) use ($to_warehouse_id) {
                $q->where('to_warehouse_id', $to_warehouse_id);
            })
            ->when($status, function($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $is_superadmin = (Auth::user()->role->name ?? '') === 'Super Administrator';
        $warehouses = $is_superadmin ? Warehouse::all() : Auth::user()->warehouses;
        $allWarehouses = Warehouse::all();
        return view('transactions.mutations.index', compact('data', 'warehouses', 'allWarehouses'));
    }

    public function show($id)
    {
        $mutation = StockMutation::with([
            'fromWarehouse', 
            'toWarehouse', 
            'user', 
            'workOrder', 
            'details.item.unit', 
            'deliveries.item.unit', 
            'deliveries.sender', 
            'deliveries.receiver'
        ])->findOrFail($id);
        return response()->json($mutation);
    }

    public function print($id)
    {
        $mutation = StockMutation::with([
            'fromWarehouse', 
            'toWarehouse', 
            'user', 
            'approver', 
            'sender', 
            'receiver', 
            'details.item.unit',
            'deliveries.item.unit', 
            'deliveries.sender', 
            'deliveries.receiver'
        ])->findOrFail($id);
        return view('transactions.mutations.print', compact('mutation'));
    }

    // 4. Laporan Rekap PM & Realisasi
    public function indexRekap(Request $request)
    {
        $from_warehouse_id = $request->from_warehouse_id;
        $to_warehouse_id = $request->to_warehouse_id;
        $status = $request->status;
        $search = $request->search;

        $query = StockMutation::with([
            'fromWarehouse:id,name',
            'toWarehouse:id,name',
            'user:id,name',
            'workOrder:id,wo_number',
            'details.item.unit',
            'deliveries.sender:id,name'
        ]);

        if ($from_warehouse_id) {
            $query->where('from_warehouse_id', $from_warehouse_id);
        }
        if ($to_warehouse_id) {
            $query->where('to_warehouse_id', $to_warehouse_id);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('workOrder', function($wq) use ($search) {
                      $wq->where('wo_number', 'like', "%{$search}%");
                  });
            });
        }

        $data = $query->latest()->paginate(10)->withQueryString();
        $warehouses = Warehouse::all();
        
        return view('transactions.mutations.rekap', compact('data', 'warehouses'));
    }

    public function deliverPartial(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0',
        ]);

        try {
            $this->inventoryService->deliverPartialMutation($id, $request->items);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Pengiriman cicilan berhasil dicatat.']);
            }
            return redirect()->back()->with('success', 'Pengiriman cicilan berhasil dicatat.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function receivePartial(Request $request, $id)
    {
        $request->validate([
            'shipment_no' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0',
        ]);

        try {
            $this->inventoryService->receivePartialMutation($id, $request->shipment_no, $request->items);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Penerimaan fisik berhasil dicatat.']);
            }
            return redirect()->back()->with('success', 'Penerimaan fisik berhasil dicatat.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
