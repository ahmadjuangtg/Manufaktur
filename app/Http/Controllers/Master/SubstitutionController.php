<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubstitutionController extends Controller
{
    public function index()
    {
        $machines = Machine::where('is_active', true)->orderBy('name')->get();
        $items = Item::with('unit')->orderBy('name')->get();
        $units = DB::table('units')->orderBy('name')->get();
        
        // Fetch all substitutions and capabilities
        $machineSubstitutions = DB::table('machine_substitutions')
            ->join('machines as m1', 'machine_substitutions.machine_id', '=', 'm1.id')
            ->join('machines as m2', 'machine_substitutions.substitute_machine_id', '=', 'm2.id')
            ->select('machine_substitutions.*', 'm1.name as machine_name', 'm2.name as substitute_name')
            ->get();

        $itemSubstitutions = DB::table('item_substitutions')
            ->join('items as i1', 'item_substitutions.item_id', '=', 'i1.id')
            ->join('units as u1', 'i1.unit_id', '=', 'u1.id')
            ->join('items as i2', 'item_substitutions.substitute_item_id', '=', 'i2.id')
            ->join('units as u2', 'i2.unit_id', '=', 'u2.id')
            ->select('item_substitutions.*', 'i1.name as item_name', 'u1.name as item_unit', 'i2.name as substitute_name', 'u2.name as substitute_unit')
            ->get();

        $capabilities = DB::table('machine_capabilities')
            ->join('machines', 'machine_capabilities.machine_id', '=', 'machines.id')
            ->join('items', 'machine_capabilities.item_id', '=', 'items.id')
            ->select('machine_capabilities.*', 'machines.name as machine_name', 'items.name as item_name')
            ->orderBy('machines.name')
            ->get();

        return view('master.substitutions.index', compact(
            'machines', 'items', 'machineSubstitutions', 'itemSubstitutions', 'capabilities', 'units'
        ));
    }

    public function storeMachine(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'substitute_machine_id' => 'required|exists:machines,id|different:machine_id',
        ]);

        DB::table('machine_substitutions')->updateOrInsert(
            ['machine_id' => $request->machine_id, 'substitute_machine_id' => $request->substitute_machine_id],
            ['notes' => $request->notes, 'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'Substitusi mesin berhasil ditambahkan');
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'substitute_item_id' => 'required|exists:items,id|different:item_id',
            'conversion_ratio' => 'required|numeric|min:0',
        ]);

        DB::table('item_substitutions')->updateOrInsert(
            ['item_id' => $request->item_id, 'substitute_item_id' => $request->substitute_item_id],
            ['conversion_ratio' => $request->conversion_ratio, 'notes' => $request->notes, 'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'Substitusi item berhasil ditambahkan');
    }

    public function storeCapability(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'item_id' => 'required|exists:items,id',
        ]);

        DB::table('machine_capabilities')->updateOrInsert(
            ['machine_id' => $request->machine_id, 'item_id' => $request->item_id],
            [
                'is_default' => $request->has('is_default'), 
                'production_rate' => $request->production_rate,
                'output_unit' => $request->output_unit,
                'capacity_unit' => $request->capacity_unit ?? 'perjam',
                'thickness' => $request->thickness,
                'diameter' => $request->diameter,
                'cavity' => $request->cavity,
                'cycle' => $request->cycle,
                'updated_at' => now(), 
                'created_at' => now()
            ]
        );

        return back()->with('success', 'Kapabilitas mesin berhasil ditambahkan');
    }

    public function destroy($type, $id)
    {
        $table = '';
        if ($type === 'machine') $table = 'machine_substitutions';
        elseif ($type === 'item') $table = 'item_substitutions';
        elseif ($type === 'capability') $table = 'machine_capabilities';

        if ($table) {
            DB::table($table)->where('id', $id)->delete();
            return back()->with('success', 'Data berhasil dihapus');
        }

        return back()->with('error', 'Tipe data tidak valid');
    }
}
