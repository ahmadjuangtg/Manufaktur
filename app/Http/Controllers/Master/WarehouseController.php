<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::query();
        if ($request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('code', 'LIKE', "%{$request->search}%");
        }

        $stats = [
            'total' => Warehouse::count(),
            'active' => Warehouse::where('is_active', true)->count()
        ];

        $data = $query->orderBy('updated_at', 'desc')->paginate(10)->withQueryString();
        return view('master.warehouses.index', compact('data', 'stats'))->with('search', $request->search);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'server_state' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'warehouse_type' => 'required',
            'area' => 'required|numeric',
            'province' => 'required',
            'city' => 'required',
            'district' => 'required',
            'village' => 'required',
        ]);

        $data = $request->all();
        $data['is_24_hours'] = $request->has('is_24_hours');
        $data['is_active'] = $request->has('is_active');
        
        // Auto-map region based on server_state
        $regionMap = [
            'WIB' => 'WEST',
            'WITA' => 'CENTRAL',
            'WIT' => 'EAST'
        ];
        $data['region'] = $regionMap[$request->server_state] ?? null;

        // Logic: if 24 hours is checked, operational_hours can be null or "24 Hours"
        if ($data['is_24_hours']) {
            $data['operational_hours'] = '24 Hours';
        }

        Warehouse::create($data);
        return redirect()->back()->with('success', 'Gudang berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'server_state' => 'required',
            'address' => 'required',
            'phone' => 'required',
        ]);

        $data = array_filter($request->all(), function($value) {
            return !is_null($value);
        });

        $data['is_24_hours'] = $request->has('is_24_hours');
        $data['is_active'] = $request->has('is_active');

        if ($request->has('server_state')) {
            $regionMap = [
                'WIB' => 'WEST',
                'WITA' => 'CENTRAL',
                'WIT' => 'EAST'
            ];
            $data['region'] = $regionMap[$request->server_state] ?? null;
        }

        if ($data['is_24_hours']) {
            $data['operational_hours'] = '24 Hours';
        }

        $warehouse->update($data);
        return redirect()->back()->with('success', 'Gudang berhasil diperbarui');
    }

    public function destroy($id)
    {
        Warehouse::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Gudang berhasil dihapus');
    }
}
