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
    public function indexRequest()
    {
        $data = StockMutation::with(['fromWarehouse', 'toWarehouse', 'user', 'workOrder'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
        
        $warehouses = Warehouse::all();
        $items = Item::with('unit')->get();
        $workOrders = WorkOrder::whereIn('status', ['pending', 'ready_to_production', 'in_progress'])
            ->latest()
            ->get();

        $selected_wo_id = request('work_order_id');
        
        return view('transactions.mutations.request', compact('data', 'warehouses', 'items', 'workOrders', 'selected_wo_id'));
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
    public function indexApproval()
    {
        $query = StockMutation::with(['fromWarehouse', 'toWarehouse', 'user', 'workOrder'])
            ->where('status', 'PENDING');

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

    public function complete($id)
    {
        try {
            $this->inventoryService->completeMutation($id);
            return redirect()->back()->with('success', 'Mutasi stok berhasil diselesaikan dan stok telah berpindah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
