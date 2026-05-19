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
    protected $productionService;

    public function __construct(\App\Services\ProductionService $productionService)
    {
        $this->productionService = $productionService;
    }

    public function index()
    {
        $workOrders = WorkOrder::with([
                'customer:id,name', 
                'products.item:id,name', 
                'stages.machine:id,name', 
                'stages.items.item:id,name'
            ])
            ->select(['id', 'wo_number', 'production_line', 'production_date', 'customer_id', 'status', 'created_at'])
            ->latest()
            ->get();
            
        return view('production.work_orders.index', compact('workOrders'));
    }

    public function create()
    {
        $customers = Customer::all();
        $products = Item::with('unit')->whereHas('type', function($q) {
            $q->where('code', 'FIN')->orWhere('name', 'Barang Jadi');
        })->get();
        $items = Item::with(['unit', 'substitutes'])->get();
        $templates = ProductionTemplate::with('product')->get();
        $machines = Machine::with(['capabilities', 'substitutes.capabilities'])->get();
        
        $today = Carbon::today()->format('Ymd');
        $lastWO = WorkOrder::whereDate('created_at', Carbon::today())->latest()->first();
        $nextNumber = $lastWO ? (int) substr($lastWO->wo_number, -4) + 1 : 1;
        $wo_number = 'WO-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('production.work_orders.create', compact('customers', 'items', 'products', 'templates', 'machines', 'wo_number'));
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
            $this->productionService->storeWorkOrder($request->all());
            return redirect()->route('production.work_orders.index')->with('success', 'Work Order created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $workOrder = WorkOrder::with(['customer', 'products.item', 'stages.machine', 'stages.items.item'])->findOrFail($id);
        return view('production.work_orders.show', compact('workOrder'));
    }

    public function destroy($id)
    {
        WorkOrder::findOrFail($id)->delete();
        return redirect()->route('production.work_orders.index')->with('success', 'Work Order deleted successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        try {
            $this->productionService->updateStatus($id, $request->status);
            return redirect()->back()->with('success', 'Work Order status updated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getTemplate($id)
    {
        $template = ProductionTemplate::with(['stages.machine', 'stages.items.item', 'products.item'])->findOrFail($id);
        return response()->json($template);
    }

    public function getStages($id)
    {
        $wo = WorkOrder::with('stages')->findOrFail($id);
        return response()->json($wo->stages);
    }
}
