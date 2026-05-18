<?php
namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\ItemRequest;
use App\Models\ItemRequestDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\StockTransaction;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller {
    // 1. REQUEST ITEMS
    public function indexRequest() {
        return view('orders.requests.index', [
            'data' => ItemRequest::with(['user', 'warehouse', 'type', 'details.item'])->where('user_id', Auth::id())->get(),
            'items' => Item::all(),
            'warehouses' => (Auth::user()->role->name ?? '') === 'Super Administrator' ? Warehouse::all() : Auth::user()->warehouses,
            'types' => Type::all()
        ]);
    }

    public function storeRequest(Request $request) {
        $request->validate(['warehouse_id' => 'required', 'items' => 'required|array']);
        
        DB::transaction(function() use ($request) {
            $ir = ItemRequest::create([
                'reference_no' => ItemRequest::generateRefNo(),
                'user_id' => Auth::id(),
                'warehouse_id' => $request->warehouse_id,
                'type_id' => $request->type_id,
                'note' => $request->note,
                'status' => 'PENDING'
            ]);

            foreach ($request->items as $item) {
                ItemRequestDetail::create([
                    'item_request_id' => $ir->id,
                    'item_id' => $item['id'],
                    'quantity' => $item['quantity']
                ]);
            }
        });

        return redirect()->back()->with('success', 'Request created successfully.');
    }

    // 2. APPROVAL REQUEST ITEM
    public function indexApproval() {
        return view('orders.approvals.index', [
            'data' => ItemRequest::with(['user', 'warehouse', 'details.item'])->where('status', 'PENDING')->get(),
            'history' => ItemRequest::with(['user', 'warehouse', 'details.item', 'approver'])
                        ->whereIn('status', ['APPROVED', 'REJECTED', 'COMPLETED'])
                        ->latest()
                        ->take(50)
                        ->get()
        ]);
    }

    public function approveRequest(Request $request, $id) {
        $ir = ItemRequest::findOrFail($id);
        $ir->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);
        return redirect()->back()->with('success', 'Request approved.');
    }

    public function rejectRequest(Request $request, $id) {
        $request->validate(['rejection_note' => 'required']);
        $ir = ItemRequest::findOrFail($id);
        $ir->update([
            'status' => 'REJECTED',
            'rejection_note' => $request->rejection_note
        ]);
        return redirect()->back()->with('success', 'Request rejected.');
    }

    public function getRequestDetails($id) {
        $ir = ItemRequest::with('details.item.unit')->findOrFail($id);
        
        foreach($ir->details as $detail) {
            $ordered = PurchaseOrderDetail::whereHas('order', function($q) use ($id) {
                $q->where('item_request_id', $id);
            })->where('item_id', $detail->item_id)->sum('quantity');
            
            $detail->quantity = max(0, $detail->quantity - $ordered);
        }
        
        // Filter out items that are already fully ordered
        $remainingDetails = $ir->details->filter(function($detail) {
            return $detail->quantity > 0;
        })->values();
        
        $ir->setRelation('details', $remainingDetails);
        
        return response()->json($ir);
    }

    public function cancelRequest($id) {
        $ir = ItemRequest::findOrFail($id);
        $ir->update(['status' => 'CANCELLED']);
        return redirect()->back()->with('success', 'Request has been cancelled and will not be processed.');
    }

    // 3. CREATE PURCHASE ORDER
    public function indexPO() {
        return view('orders.purchase_orders.index', [
            'data' => PurchaseOrder::with(['supplier', 'user', 'request', 'details.item'])->get(),
            'suppliers' => Supplier::all(),
            'items' => Item::all(),
            'types' => Type::all(),
            'approvedRequests' => ItemRequest::whereIn('status', ['APPROVED', 'PARTIAL'])->get()
        ]);
    }

    public function storePO(Request $request) {
        $request->validate(['supplier_id' => 'required', 'order_date' => 'required', 'item_request_id' => 'nullable']);
        
        DB::transaction(function() use ($request) {
            $po = PurchaseOrder::create([
                'po_no' => PurchaseOrder::generatePONo(),
                'item_request_id' => $request->item_request_id,
                'supplier_id' => $request->supplier_id,
                'user_id' => Auth::id(),
                'order_date' => $request->order_date,
                'status' => 'OPEN'
            ]);

            if ($request->items) {
                foreach ($request->items as $item) {
                    if (empty($item['id'])) continue;
                    PurchaseOrderDetail::create([
                        'purchase_order_id' => $po->id,
                        'item_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'] ?? 0
                    ]);
                }
                
                if ($request->item_request_id) {
                    $ir = ItemRequest::with('details')->find($request->item_request_id);
                    $allFulfilled = true;
                    foreach($ir->details as $detail) {
                        $ordered = PurchaseOrderDetail::whereHas('order', function($q) use ($ir) {
                            $q->where('item_request_id', $ir->id);
                        })->where('item_id', $detail->item_id)->sum('quantity');
                        
                        if ($ordered < $detail->quantity) {
                            $allFulfilled = false;
                            break;
                        }
                    }
                    $ir->update(['status' => $allFulfilled ? 'COMPLETED' : 'PARTIAL']);
                }
            } elseif ($request->item_request_id) {
                $ir = ItemRequest::with('details')->findOrFail($request->item_request_id);
                foreach ($ir->details as $detail) {
                    // Check remaining for direct fulfillment (if any)
                    $ordered = PurchaseOrderDetail::whereHas('order', function($q) use ($ir) {
                        $q->where('item_request_id', $ir->id);
                    })->where('item_id', $detail->item_id)->sum('quantity');
                    
                    $remaining = $detail->quantity - $ordered;
                    if ($remaining <= 0) continue;

                    $latestPrice = \App\Models\PriceList::where('item_id', $detail->item_id)->where('is_active', true)->latest()->first();
                    PurchaseOrderDetail::create([
                        'purchase_order_id' => $po->id,
                        'item_id' => $detail->item_id,
                        'quantity' => $remaining,
                        'price' => $latestPrice ? $latestPrice->hna_ppn : 0
                    ]);
                }
                $ir->update(['status' => 'COMPLETED']); // Direct fulfillment always completes
            }

            \Log::info('PO Store Request:', $request->all());

            // Update total amount and create SHADOW_IN
            $total = 0;
            
            // Determine warehouse for shadow stock (from request or fallback to first warehouse)
            $shadowWarehouseId = null;
            if ($request->item_request_id) {
                $ir = ItemRequest::find($request->item_request_id);
                if ($ir) $shadowWarehouseId = $ir->warehouse_id;
            }
            if (!$shadowWarehouseId) {
                $firstWarehouse = \App\Models\Warehouse::first();
                $shadowWarehouseId = $firstWarehouse ? $firstWarehouse->id : 1;
            }

            foreach ($po->details as $detail) {
                $total += $detail->quantity * $detail->price;
                
                // Add Shadow Stock Expectation
                StockTransaction::create([
                    'warehouse_id' => $shadowWarehouseId,
                    'item_id' => $detail->item_id,
                    'type' => 'SHADOW_IN',
                    'quantity' => $detail->quantity,
                    'reference_no' => $po->po_no,
                    'user_id' => Auth::id(),
                    'note' => 'Ekspektasi Kedatangan PO: ' . $po->po_no
                ]);
            }
            $po->update(['total_amount' => $total]);
        });

        return redirect()->back()->with('success', 'Purchase Order created.');
    }

    // 4. RECEIVE MATERIAL
    public function indexReceive() {
        return view('orders.receives.index', [
            'data' => PurchaseOrder::with(['supplier', 'details.item.unit', 'request'])->whereIn('status', ['OPEN', 'PARTIAL'])->get(),
            'items' => Item::with('unit')->get(),
            'warehouses' => (Auth::user()->role->name ?? '') === 'Super Administrator' ? Warehouse::all() : Auth::user()->warehouses
        ]);
    }

    public function storeReceive(Request $request, $id) {
        $po = PurchaseOrder::findOrFail($id);
        $request->validate([
            'warehouse_id' => 'required',
            'items' => 'nullable|array',
            'extra_items' => 'nullable|array',
            'note' => 'nullable|string'
        ]);

        DB::transaction(function() use ($request, $po) {
            $warehouseId = $request->warehouse_id;
            
            // 1. Process regular items
            if ($request->items) {
                foreach ($request->items as $itemId => $qty) {
                    if ($qty <= 0) continue;

                    $detail = $po->details()->where('item_id', $itemId)->first();
                    if ($detail) {
                        $detail->increment('received_quantity', $qty);
                        
                        // Decrease SHADOW_IN (Find where it was originally logged)
                        $shadowTx = StockTransaction::where('reference_no', $po->po_no)
                            ->where('item_id', $itemId)
                            ->where('type', 'SHADOW_IN')
                            ->first();
                            
                        if ($shadowTx) {
                            StockTransaction::create([
                                'warehouse_id' => $shadowTx->warehouse_id,
                                'item_id' => $itemId,
                                'type' => 'SHADOW_OUT',
                                'quantity' => $qty,
                                'reference_no' => $po->po_no,
                                'user_id' => Auth::id(),
                                'note' => ($request->note ? $request->note . ' | ' : '') . 'Realisasi Kedatangan PO: ' . $po->po_no
                            ]);
                        }

                        $this->createStockTransaction($po, $itemId, $qty, $warehouseId);
                    }
                }
            }

            // 2. Process extra items (Bonuses)
            if ($request->extra_items) {
                foreach ($request->extra_items as $extra) {
                    if (empty($extra['id']) || empty($extra['quantity']) || $extra['quantity'] <= 0) continue;

                    $itemId = $extra['id'];
                    $qty = $extra['quantity'];

                    // Bonuses do NOT change PO details, they only create Stock In transactions
                    $this->createStockTransaction($po, $itemId, $qty, $warehouseId, 'Bonus Received from PO: ' . $po->po_no);
                }
            }

            // 3. Update Status (Based only on original PO items)
            $totalOrdered = $po->details()->sum('quantity');
            $totalReceived = $po->details()->sum('received_quantity');
            $status = ($totalReceived >= $totalOrdered) ? 'CLOSED' : 'PARTIAL';
            $po->update(['status' => $status]);
        });

        return redirect()->back()->with('success', 'Materials received and stock updated.');
    }

    private function createStockTransaction($po, $itemId, $qty, $warehouseId, $note = null) {
        $customNote = request('note');
        StockTransaction::create([
            'warehouse_id' => $warehouseId,
            'item_id' => $itemId,
            'type' => 'IN',
            'quantity' => $qty,
            'reference_no' => $po->po_no,
            'user_id' => Auth::id(),
            'note' => ($customNote ? $customNote . ' | ' : '') . ($note ?? 'Received from PO: ' . $po->po_no)
        ]);
    }
}
