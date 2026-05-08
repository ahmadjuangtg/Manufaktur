<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $data = Warehouse::all();
        return view('master.warehouses.index', compact('data'));
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
            'region' => 'required',
        ]);

        $data = $request->all();
        $data['is_24_hours'] = $request->has('is_24_hours');
        $data['is_active'] = $request->has('is_active');
        
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
