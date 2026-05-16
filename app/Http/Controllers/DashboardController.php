<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('dashboard_view')) {
            $user = auth()->user();
            if ($user->hasPermission('shop_floor_view')) return redirect()->route('shop_floor.index');
            if ($user->hasPermission('inventory_view')) return redirect()->route('inventory.index');
            if ($user->hasPermission('order_view')) return redirect()->route('orders.requests.index');
            if ($user->hasPermission('production_wo_view')) return redirect()->route('production.work_orders.index');
            
            // If no module access, show a simple welcome message
            return view('welcome_simple');
        }

        // 1. Item & Stock Stats
        $total_sku = Item::count();
        
        $total_stock = InventoryStock::sum('current_stock');

        $stock_by_category = \App\Models\Category::leftJoin('items', 'categories.id', '=', 'items.category_id')
            ->leftJoin('inventory_stocks', 'items.id', '=', 'inventory_stocks.item_id')
            ->select('categories.name')
            ->selectRaw('SUM(inventory_stocks.current_stock) as balance')
            ->groupBy('categories.id', 'categories.name')
            ->having('balance', '>', 0)
            ->orderBy('balance', 'desc')
            ->get()
            ->map(function($row) {
                return [
                    'name' => $row->name,
                    'balance' => $row->balance ?? 0
                ];
            });

        // 2. Manufacturing Stats
        $total_manufacturers = \App\Models\Manufacturer::count();

        // 3. Work Order Stats
        $wo_counts = \App\Models\WorkOrder::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $wo_stats = [
            'pending' => $wo_counts['pending'] ?? 0,
            'ready' => $wo_counts['ready_to_production'] ?? 0,
            'in_progress' => $wo_counts['in_progress'] ?? 0,
            'completed' => $wo_counts['completed'] ?? 0,
        ];

        // 4. Active Production Details
        $active_productions = \App\Models\WorkOrder::with(['products.item', 'stages' => function($q) {
            $q->where('status', 'in_progress');
        }])
        ->where('status', 'in_progress')
        ->latest()
        ->get();

        $stats = [
            'total_sku' => $total_sku,
            'total_stock' => $total_stock,
            'total_manufacturers' => $total_manufacturers,
            'wo_stats' => $wo_stats,
            'stock_by_category' => $stock_by_category,
            'active_productions' => $active_productions,
            'recent_transactions' => \App\Models\StockTransaction::with(['item', 'warehouse'])->latest()->take(5)->get(),
        ];
        
        return view('dashboard', compact('stats'));
    }
}
