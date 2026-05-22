<?php

namespace App\Http\Controllers;

use App\Models\PackingList;
use App\Models\PackingListDetail;
use App\Models\DeliveryBatch;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogisticsController extends Controller
{
    // --- Packing List ---
    public function indexPacking()
    {
        $data = PackingList::with(['details.item', 'customer', 'user', 'deliveryBatch'])->latest()->get();
        $items = Item::with('unit')
            ->whereHas('stocks', function ($query) {
                $query->where('current_stock', '>', 0);
            })->get();
        $customers = \App\Models\Customer::all();
        return view('logistics.packing.index', compact('data', 'items', 'customers'));
    }

    public function storePacking(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request) {
            $packing = PackingList::create([
                'packing_no' => 'PKG-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'customer_id' => $request->customer_id,
                'status' => 'DRAFT',
                'note' => $request->note,
                'user_id' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                PackingListDetail::create([
                    'packing_list_id' => $packing->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'package_type' => $item['package_type'] ?? 'Box',
                    'package_number' => $item['package_number'] ?? null,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Packing List berhasil dibuat.');
    }

    public function updateStatusPacking($id)
    {
        $packing = PackingList::findOrFail($id);
        $packing->update(['status' => 'READY']);
        return redirect()->back()->with('success', 'Packing List siap untuk dikirim.');
    }

    // --- Delivery Batch ---
    public function indexDelivery(Request $request)
    {
        $query = DeliveryBatch::with(['packingLists.customer', 'user']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('batch_no', 'like', "%{$search}%")
                  ->orWhere('driver_name', 'like', "%{$search}%")
                  ->orWhere('vehicle_no', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhereHas('packingLists.customer', function($qc) use ($search) {
                      $qc->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest()->paginate(10)->withQueryString();
        $availablePackingLists = PackingList::with('customer')->whereNull('delivery_batch_id')->where('status', 'READY')->get();
        $customers = \App\Models\Customer::all();
        
        return view('logistics.delivery.index', compact('data', 'availablePackingLists', 'customers'))
            ->with('search', $request->search)
            ->with('status', $request->status);
    }

    public function storeDelivery(Request $request)
    {
        $request->validate([
            'destination' => 'nullable',
            'packing_list_ids' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $pls = PackingList::with('customer')->whereIn('id', $request->packing_list_ids)->get();
            
            $dest = $request->destination;
            if (empty($dest)) {
                $destParts = [];
                $index = 1;
                foreach ($pls as $pl) {
                    if ($pl->customer) {
                        $destParts[] = "{$index}. {$pl->customer->name} ({$pl->customer->address})";
                        $index++;
                    }
                }
                $dest = implode("\n", $destParts);
            }

            $batch = DeliveryBatch::create([
                'batch_no' => 'DEL-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'destination' => $dest ?: 'Multi-Customer Delivery',
                'driver_name' => $request->driver_name,
                'vehicle_no' => $request->vehicle_no,
                'status' => 'PENDING',
                'user_id' => Auth::id(),
            ]);

            PackingList::whereIn('id', $request->packing_list_ids)
                ->update(['delivery_batch_id' => $batch->id, 'status' => 'SHIPPED']);
        });

        return redirect()->back()->with('success', 'Delivery Batch berhasil dibuat.');
    }

    public function indexTracking()
    {
        $data = DeliveryBatch::with(['packingLists.customer', 'packingLists.details.item', 'user'])
            ->whereIn('status', ['PENDING', 'ON_DELIVERY'])
            ->latest()
            ->get();
        return view('logistics.tracking.index', compact('data'));
    }

    public function updateStatusDelivery(Request $request, $id)
    {
        $batch = DeliveryBatch::with('packingLists.details.item')->findOrFail($id);
        $oldStatus = $batch->status;
        $status = $request->status;

        if ($oldStatus === $status) return redirect()->back();

        $updateData = ['status' => $status];
        if ($status == 'ON_DELIVERY') {
            $updateData['departure_at'] = now();

            // Find Finished Goods Warehouse
            $fgWh = \App\Models\Warehouse::where('name', 'like', '%Barang Jadi%')->first();
            $warehouseId = $fgWh ? $fgWh->id : 1; // Fallback to ID 1
            $warehouseName = $fgWh ? $fgWh->name : 'Gudang Barang Jadi';

            // Validate stock availability for all items in the batch
            foreach ($batch->packingLists as $pl) {
                foreach ($pl->details as $detail) {
                    $stock = \App\Models\InventoryStock::where('item_id', $detail->item_id)
                        ->where('warehouse_id', $warehouseId)
                        ->first();
                    
                    $available = $stock ? $stock->current_stock : 0;
                    if ($available < $detail->quantity) {
                        $itemName = $detail->item->name ?? 'Barang';
                        return redirect()->back()->with('error', "Stok fisik di {$warehouseName} tidak mencukupi untuk {$itemName}. Tersedia: {$available}, Diminta: {$detail->quantity}. Silakan lakukan NPB/PHP/Mutasi Gudang terlebih dahulu.");
                    }
                }
            }

            // REDUCE STOCK FROM FG WAREHOUSE
            DB::transaction(function() use ($batch, $warehouseId) {
                foreach ($batch->packingLists as $pl) {
                    foreach ($pl->details as $detail) {
                        \App\Models\StockTransaction::create([
                            'item_id' => $detail->item_id,
                            'warehouse_id' => $warehouseId,
                            'type' => 'OUT',
                            'quantity' => $detail->quantity,
                            'reference_no' => $batch->batch_no,
                            'user_id' => Auth::id(),
                            'note' => 'Pengiriman Barang: ' . $batch->batch_no . ' (Packing: ' . $pl->packing_no . ')'
                        ]);
                    }
                }
            });
        }
        
        if ($status == 'COMPLETED') $updateData['arrival_at'] = now();

        $batch->update($updateData);

        return redirect()->back()->with('success', 'Status pengiriman diperbarui ke ' . $status . ' dan stok telah diperbarui.');
    }

    public function printSuratJalan($id)
    {
        $packing = PackingList::with(['details.item.unit', 'customer', 'deliveryBatch'])->findOrFail($id);
        return view('logistics.delivery.print_surat_jalan', compact('packing'));
    }
}
