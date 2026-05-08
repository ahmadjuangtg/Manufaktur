<?php

namespace App\Http\Controllers;

use App\Models\ProductionTemplate;
use App\Models\ProductionTemplateStage;
use App\Models\ProductionTemplateItem;
use App\Models\ProductionTemplateProduct;
use App\Models\Item;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionTemplateController extends Controller
{
    public function index()
    {
        $templates = ProductionTemplate::with(['product', 'products.item', 'stages.items.item'])->latest()->get();
        return view('production.templates.index', compact('templates'));
    }

    public function create()
    {
        // Product must be "Barang Jadi"
        $finishedGoods = Item::with('unit')->whereHas('type', function($q) {
            $q->where('code', 'FIN')->orWhere('name', 'Barang Jadi');
        })->get();
        
        $items = Item::with('unit')->get();
        $machines = Machine::all();
        
        return view('production.templates.create', compact('finishedGoods', 'items', 'machines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:production_templates,code',
            'name' => 'required',
            'production_line' => 'nullable|integer|between:1,4',
            'products' => 'required|array|min:1',
            'stages' => 'required|array|min:1',
            'stages.*.name' => 'required',
        ]);

        DB::transaction(function() use ($request) {
            $template = ProductionTemplate::create([
                'code' => $request->code,
                'name' => $request->name,
                'product_id' => $request->products[0]['item_id'] ?? null,
                'production_line' => $request->production_line,
                'marketing' => $request->marketing,
                'duration' => $request->duration,
                'stage_code' => $request->stage_code,
                'composition_code' => $request->composition_code,
                'notes' => $request->notes,
            ]);

            foreach ($request->products as $prodData) {
                ProductionTemplateProduct::create([
                    'template_id' => $template->id,
                    'item_id' => $prodData['item_id'],
                    'quantity' => $prodData['quantity'] ?? 0
                ]);
            }

            foreach ($request->stages as $index => $stageData) {
                $stage = ProductionTemplateStage::create([
                    'template_id' => $template->id,
                    'name' => $stageData['name'],
                    'sequence' => $index + 1,
                    'machine_id' => $stageData['machine_id']
                ]);

                if (isset($stageData['items'])) {
                    foreach ($stageData['items'] as $itemData) {
                        ProductionTemplateItem::create([
                            'stage_id' => $stage->id,
                            'item_id' => $itemData['item_id'],
                            'quantity_per_batch' => $itemData['quantity_per_batch'] ?? 0,
                            'type' => $itemData['type'] ?? 'input'
                        ]);
                    }
                }
            }
        });

        return redirect()->route('production.templates.index')->with('success', 'Production Template created successfully.');
    }

    public function show($id)
    {
        try {
            $template = ProductionTemplate::findOrFail($id);
            
            // Load relations one by one to find the culprit
            $template->load('product');
            $template->load('products.item');
            $template->load('stages.items.item');
            $template->load('stages.machine');
            
            return response()->json($template);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);
        }
    }

    public function edit($id)
    {
        $template = ProductionTemplate::with(['products.item', 'stages.items.item'])->findOrFail($id);
        $finishedGoods = Item::with('unit')->whereHas('type', function($q) {
            $q->where('code', 'FG')->orWhere('name', 'Barang Jadi');
        })->get();
        $items = Item::with('unit')->get();
        $machines = Machine::all();

        return view('production.templates.edit', compact('template', 'finishedGoods', 'items', 'machines'));
    }

    public function update(Request $request, $id)
    {
        $template = ProductionTemplate::findOrFail($id);
        
        $request->validate([
            'code' => 'required|unique:production_templates,code,' . $id,
            'name' => 'required',
            'production_line' => 'nullable|integer|between:1,4',
            'products' => 'required|array|min:1',
            'stages' => 'required|array|min:1',
        ]);

        DB::transaction(function() use ($request, $template) {
            $template->update([
                'code' => $request->code,
                'name' => $request->name,
                'product_id' => $request->products[0]['item_id'] ?? null,
                'production_line' => $request->production_line,
                'marketing' => $request->marketing,
                'duration' => $request->duration,
                'stage_code' => $request->stage_code,
                'composition_code' => $request->composition_code,
                'notes' => $request->notes,
            ]);

            // Sync products
            $template->products()->delete();
            foreach ($request->products as $prodData) {
                ProductionTemplateProduct::create([
                    'template_id' => $template->id,
                    'item_id' => $prodData['item_id'],
                    'quantity' => $prodData['quantity'] ?? 0
                ]);
            }

            // Delete old stages and items
            foreach ($template->stages as $stage) {
                $stage->items()->delete();
            }
            $template->stages()->delete();

            foreach ($request->stages as $index => $stageData) {
                $stage = ProductionTemplateStage::create([
                    'template_id' => $template->id,
                    'name' => $stageData['name'],
                    'sequence' => $index + 1,
                    'machine_id' => $stageData['machine_id']
                ]);

                if (isset($stageData['items'])) {
                    foreach ($stageData['items'] as $itemData) {
                        ProductionTemplateItem::create([
                            'stage_id' => $stage->id,
                            'item_id' => $itemData['item_id'],
                            'quantity_per_batch' => $itemData['quantity_per_batch'] ?? 0,
                            'type' => $itemData['type'] ?? 'input'
                        ]);
                    }
                }
            }
        });

        return redirect()->route('production.templates.index')->with('success', 'Production Template updated successfully.');
    }

    public function destroy($id)
    {
        $template = ProductionTemplate::findOrFail($id);
        DB::transaction(function() use ($template) {
            foreach ($template->stages as $stage) {
                $stage->items()->delete();
            }
            $template->stages()->delete();
            $template->delete();
        });

        return redirect()->route('production.templates.index')->with('success', 'Production Template deleted successfully.');
    }
}
