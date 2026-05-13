<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\MachineCategory;
use App\Models\Warehouse;
use App\Models\Supplier;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        $query = Machine::query()->with(['category', 'steps', 'supplier']);
        if ($request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('code', 'LIKE', "%{$request->search}%");
        }

        $stats = [
            'total' => Machine::count(),
            'active' => Machine::where('is_active', true)->count()
        ];

        $data = $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString();
        $categories = MachineCategory::all();
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $units = \App\Models\Unit::all();
        return view('master.machines.index', compact('data', 'categories', 'suppliers', 'warehouses', 'units', 'stats'))->with('search', $request->search);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'machine_category_id' => 'required',
            'capacity' => 'required|numeric',
            'capacity_unit' => 'required',
            'output_unit' => 'required',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'steps' => 'nullable|array',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $data = $request->all();
            $data['is_active'] = $request->has('is_active');
            $machine = Machine::create($data);

            if ($request->has('steps')) {
                foreach ($request->steps as $index => $stepName) {
                    if ($stepName) {
                        $machine->steps()->create([
                            'step_name' => $stepName,
                            'sequence' => $index + 1
                        ]);
                    }
                }
            }
        });

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
            'supplier_id' => 'nullable|exists:suppliers,id',
            'steps' => 'nullable|array',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $machine) {
            $data = $request->all();
            $data['is_active'] = $request->has('is_active');
            $machine->update($data);

            // Refresh steps
            $machine->steps()->delete();
            if ($request->has('steps')) {
                foreach ($request->steps as $index => $stepName) {
                    if ($stepName) {
                        $machine->steps()->create([
                            'step_name' => $stepName,
                            'sequence' => $index + 1
                        ]);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Mesin berhasil diperbarui');
    }

    public function destroy($id)
    {
        Machine::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Mesin berhasil dihapus');
    }
}
