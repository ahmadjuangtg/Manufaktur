<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\MachineCategory;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        $data = Machine::with('category')->get();
        $categories = MachineCategory::all();
        $warehouses = Warehouse::all();
        $units = \App\Models\Unit::all();
        return view('master.machines.index', compact('data', 'categories', 'warehouses', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'machine_category_id' => 'required',
            'capacity' => 'required|numeric',
            'capacity_unit' => 'required',
            'output_unit' => 'required',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        
        Machine::create($data);
        return redirect()->back()->with('success', 'Mesin berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);
        $request->validate([
            'name' => 'required',
            'machine_category_id' => 'required',
            'capacity' => 'required|numeric',
            'capacity_unit' => 'required',
            'output_unit' => 'required',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        
        $machine->update($data);
        return redirect()->back()->with('success', 'Mesin berhasil diperbarui');
    }

    public function destroy($id)
    {
        Machine::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Mesin berhasil dihapus');
    }
}
