<?php

namespace App\Http\Controllers;

use App\Models\Priority;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    public function index()
    {
        return view('master.priorities.index', [
            'data' => Priority::orderBy('level', 'asc')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'level' => 'required|integer|between:1,5',
            'name' => 'required|string|max:255',
            'color' => 'nullable|string'
        ]);

        Priority::create([
            'code' => Priority::generateCode(),
            'level' => $request->level,
            'name' => $request->name,
            'color' => $request->color ?? '#6366f1'
        ]);

        return redirect()->back()->with('success', 'Priority created successfully.');
    }

    public function update(Request $request, $id)
    {
        $priority = Priority::findOrFail($id);
        
        $request->validate([
            'level' => 'required|integer|between:1,5',
            'name' => 'required|string|max:255',
            'color' => 'nullable|string'
        ]);

        $priority->update($request->all());

        return redirect()->back()->with('success', 'Priority updated successfully.');
    }

    public function destroy($id)
    {
        Priority::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Priority deleted successfully.');
    }
}
