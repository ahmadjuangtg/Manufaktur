<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MachineCategory;
use Illuminate\Http\Request;

class MachineCategoryController extends Controller
{
    public function index()
    {
        $data = MachineCategory::all();
        return view('master.machine_categories.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $data = $request->all();
        $data['code'] = MachineCategory::generateCode();

        MachineCategory::create($data);
        return redirect()->back()->with('success', 'Kategori Mesin berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $category = MachineCategory::findOrFail($id);
        $request->validate([
            'name' => 'required',
        ]);

        $category->update($request->all());
        return redirect()->back()->with('success', 'Kategori Mesin berhasil diperbarui');
    }

    public function destroy($id)
    {
        MachineCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Kategori Mesin berhasil dihapus');
    }
}
