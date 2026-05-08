<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\WorkOrderProduct;
use App\Models\WorkOrderStage;
use App\Models\WorkOrderStageItem;
use App\Models\ProductionTemplate;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Machine;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductionController extends Controller
{
    public function index()
    {
        $workOrders = WorkOrder::with('customer', 'products.item')->latest()->get();
        return view('production.work_orders.index', compact('workOrders'));
    }

    public function create()
    {
        $customers = Customer::all();
        
        // Products must be "Barang Jadi" (Type Code "FG" or Name "Barang Jadi")
        $products = Item::with('unit')->whereHas('type', function($q) {
            $q->where('code', 'FIN')->orWhere('name', 'Barang Jadi');
        })->get();
        
        // All items for material allocation
        $items = Item::with('unit')->get();
        
        $templates = ProductionTemplate::with('product')->get();
        $machines = Machine::all();
        
        // Auto-generate WO Number: WO-YYYYMMDD-0001
        $today = Carbon::today()->format('Ymd');
        $lastWO = WorkOrder::whereDate('created_at', Carbon::today())->latest()->first();
        $nextNumber = $lastWO ? (int) substr($lastWO->wo_number, -4) + 1 : 1;
        $wo_number = 'WO-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('production.work_orders.create', [
            'customers' => $customers,
            'items' => $items,
            'products' => $products,
            'templates' => $templates,
            'machines' => $machines,
            'wo_number' => $wo_number
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'wo_number' => 'required|unique:work_orders',
            'production_line' => 'required|integer|between:1,4',
            'production_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'products' => 'required|array|min:1',
            'stages' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $workOrder = WorkOrder::create([
                'wo_number' => $request->wo_number,
                'production_line' => $request->production_line,
                'production_date' => $request->production_date,
                'customer_id' => $request->customer_id,
                'marketing' => $request->marketing,
                'total_batch' => $request->total_batch ?? 1,
                'duration' => $request->duration,
                'stage_code' => $request->stage_code,
                'composition_code' => $request->composition_code,
                'notes' => $request->notes,
                'status' => 'pending'
            ]);

            foreach ($request->products as $prod) {
                WorkOrderProduct::create([
                    'work_order_id' => $workOrder->id,
                    'item_id' => $prod['item_id'],
                    'quantity' => $prod['quantity']
                ]);
            }

            foreach ($request->stages as $index => $stageData) {
                $stage = WorkOrderStage::create([
                    'work_order_id' => $workOrder->id,
                    'name' => $stageData['name'],
                    'sequence' => $index + 1,
                    'machine_id' => $stageData['machine_id'] ?? null,
                    'duration_hours' => $stageData['duration_hours'] ?? null,
                    'planned_start' => $stageData['planned_start'] ?? null,
                    'status' => 'pending'
                ]);

                if (isset($stageData['items'])) {
                    foreach ($stageData['items'] as $itemData) {
                        WorkOrderStageItem::create([
                            'work_order_stage_id' => $stage->id,
                            'item_id' => $itemData['item_id'],
                            'quantity_per_batch' => $itemData['quantity_per_batch'],
                            'quantity_total' => $itemData['quantity_per_batch'] * ($request->total_batch ?? 1),
                            'type' => $itemData['type'] // 'input' or 'output'
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('production.work_orders.index')->with('success', 'Work Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function getTemplate($id)
    {
        $template = ProductionTemplate::with(['stages.items.item', 'stages.machine', 'products.item'])->find($id);
        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }
        return response()->json($template);
    }
    public function updateStatus(Request $request, $id)
    {
        $wo = WorkOrder::with(['products', 'stages.items'])->findOrFail($id);
        $status = $request->status;

        if (!in_array($status, ['in_progress', 'completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        try {
            DB::beginTransaction();

            $wo->update(['status' => $status]);

            if ($status === 'completed') {
                // 1. Increase FG Stock
                foreach ($wo->products as $product) {
                    StockTransaction::create([
                        'item_id' => $product->item_id,
                        'warehouse_id' => 1, // Default to main warehouse
                        'type' => 'IN',
                        'quantity' => $product->quantity,
                        'reference_no' => $wo->wo_number,
                        'user_id' => auth()->id(),
                        'note' => 'Production Completion: ' . $wo->wo_number
                    ]);
                }

                // 2. Decrease Raw Material Stock
                foreach ($wo->stages as $stage) {
                    foreach ($stage->items as $item) {
                        if ($item->type === 'input' || $item->type === 'INPUT') {
                            StockTransaction::create([
                                'item_id' => $item->item_id,
                                'warehouse_id' => 1, // Default to main
                                'type' => 'OUT',
                                'quantity' => $item->quantity_total,
                                'reference_no' => $wo->wo_number,
                                'user_id' => auth()->id(),
                                'note' => 'Production Consumption: ' . $wo->wo_number
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Work Order status updated to ' . str_replace('_', ' ', $status));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
