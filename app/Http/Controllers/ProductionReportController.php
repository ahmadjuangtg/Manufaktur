<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\ProductionOutput;
use App\Models\ProductionTransfer;
use App\Models\Warehouse;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionReportController extends Controller
{
    public function indexLHP()
    {
        $data = ProductionOutput::with(['workOrder', 'stage', 'operator'])->latest()->get();
        return view('production.reports.lhp', compact('data'));
    }

    public function indexHandover()
    {
        $data = ProductionTransfer::with(['workOrder', 'fromWarehouse', 'toWarehouse', 'requester', 'verifier'])->latest()->get();
        $workOrders = WorkOrder::where('status', 'completed')->get(); // Only completed WOs can be transferred
        $warehouses = Warehouse::all();
        return view('production.reports.handover', compact('data', 'workOrders', 'warehouses'));
    }

    public function storeHandover(Request $request)
    {
        $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'type' => 'required|in:NPB,PHP',
            'quantity' => 'required|numeric|min:0.01',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $wo = WorkOrder::findOrFail($request->work_order_id);

        ProductionTransfer::create([
            'reference_no' => ($request->type === 'PHP' ? 'PHP-' : 'NPB-') . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
            'work_order_id' => $request->work_order_id,
            'type' => $request->type,
            'quantity' => $request->quantity,
            'from_warehouse_id' => $request->from_warehouse_id,
            'to_warehouse_id' => $request->to_warehouse_id,
            'status' => 'PENDING',
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Form serah terima (' . $request->type . ') berhasil diajukan.');
    }

    public function verifyHandover(Request $request, $id)
    {
        $transfer = ProductionTransfer::findOrFail($id);
        
        DB::transaction(function () use ($transfer) {
            $transfer->update([
                'status' => 'VERIFIED',
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            // If it's PHP (to FG Warehouse), update the actual stock
            // We need to know WHICH item is being transferred.
            // Usually it's the product of the Work Order.
            $wo = $transfer->workOrder;
            foreach ($wo->products as $product) {
                // Increase stock in TO warehouse
                StockTransaction::create([
                    'item_id' => $product->item_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'type' => 'IN',
                    'quantity' => $transfer->quantity, // Usually based on total produced
                    'reference_no' => $transfer->reference_no,
                    'user_id' => Auth::id(),
                    'note' => 'Penerimaan Hasil Produksi (PHP): ' . $wo->wo_number
                ]);
                
                // If it came from another warehouse, decrease stock there
                // (e.g., from WIP warehouse)
                StockTransaction::create([
                    'item_id' => $product->item_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'type' => 'OUT',
                    'quantity' => $transfer->quantity,
                    'reference_no' => $transfer->reference_no,
                    'user_id' => Auth::id(),
                    'note' => 'Penyerahan Barang Produksi (NPB/PHP): ' . $wo->wo_number
                ]);
            }
        });

        return redirect()->back()->with('success', 'Serah terima berhasil diverifikasi dan stok telah diperbarui.');
    }
}
