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
        $workOrders = WorkOrder::whereIn('status', ['in_progress', 'completed'])->get(); 
        $warehouses = Warehouse::all();
        return view('production.reports.handover', compact('data', 'workOrders', 'warehouses'));
    }

    public function storeHandover(Request $request)
    {
        $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'work_order_stage_id' => 'nullable|exists:work_order_stages,id',
            'type' => 'required|in:NPB,PHP',
            'quantity' => 'required|numeric|min:0.01',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $wo = WorkOrder::findOrFail($request->work_order_id);

        ProductionTransfer::create([
            'reference_no' => ($request->type === 'PHP' ? 'PHP-' : 'NPB-') . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
            'work_order_id' => $request->work_order_id,
            'work_order_stage_id' => $request->work_order_stage_id,
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

            $wo = $transfer->workOrder;
            $itemsToTransfer = [];

            // Case A: Handover for a specific STAGE (WIP / Intermediate Output)
            if ($transfer->work_order_stage_id) {
                $stage = \App\Models\WorkOrderStage::with('items')->find($transfer->work_order_stage_id);
                $outputs = $stage->items->where('type', 'output');
                foreach ($outputs as $out) {
                    $itemsToTransfer[] = [
                        'item_id' => $out->item_id,
                        'quantity' => $transfer->quantity // Usually matches the report qty
                    ];
                }
            } 
            // Case B: Handover for the entire WO (Finished Goods)
            else {
                foreach ($wo->products as $product) {
                    $itemsToTransfer[] = [
                        'item_id' => $product->item_id,
                        'quantity' => $transfer->quantity
                    ];
                }
            }

            foreach ($itemsToTransfer as $target) {
                // 1. Increase stock in TO warehouse
                StockTransaction::create([
                    'item_id' => $target['item_id'],
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'type' => 'IN',
                    'quantity' => $target['quantity'],
                    'reference_no' => $transfer->reference_no,
                    'user_id' => Auth::id(),
                    'note' => 'Penerimaan Hasil Produksi (' . $transfer->type . '): ' . $wo->wo_number
                ]);
                
                // 2. Decrease stock in FROM warehouse
                StockTransaction::create([
                    'item_id' => $target['item_id'],
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'type' => 'OUT',
                    'quantity' => $target['quantity'],
                    'reference_no' => $transfer->reference_no,
                    'user_id' => Auth::id(),
                    'note' => 'Penyerahan Hasil Produksi (' . $transfer->type . '): ' . $wo->wo_number
                ]);
            }
        });

        return redirect()->back()->with('success', 'Serah terima berhasil diverifikasi dan stok telah diperbarui.');
    }

    public function printHandover($id)
    {
        $transfer = ProductionTransfer::with(['workOrder.products.item', 'fromWarehouse', 'toWarehouse', 'requester', 'verifier'])->findOrFail($id);
        return view('production.reports.handover_print', compact('transfer'));
    }
}
