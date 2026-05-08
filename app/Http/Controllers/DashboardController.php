<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_items' => Item::count(),
            'total_warehouses' => Warehouse::count(),
            'total_transactions' => StockTransaction::count(),
            'recent_transactions' => StockTransaction::with(['item', 'warehouse'])->latest()->take(5)->get(),
        ];
        
        return view('dashboard', compact('stats'));
    }
}
