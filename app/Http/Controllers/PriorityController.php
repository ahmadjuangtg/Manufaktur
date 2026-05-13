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
            'level' => 'required|integer|between:1,10',
            'name' => 'required|string|max:255',
            'color' => 'nullable|string'
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $newLevel = $request->level;
            
            // Shift existing priorities up (increment level)
            $existingPriorities = Priority::where('level', '>=', $newLevel)
                ->orderBy('level', 'desc')
                ->get();
                
            foreach ($existingPriorities as $p) {
                $p->increment('level');
            }

            Priority::create([
                'code' => Priority::generateCode(),
                'level' => $newLevel,
                'name' => $request->name,
                'color' => $request->color ?? '#6366f1'
            ]);
        });

        return redirect()->back()->with('success', 'Priority created and levels re-sequenced.');
    }

    public function update(Request $request, $id)
    {
        $priority = Priority::findOrFail($id);
        
        $request->validate([
            'level' => 'required|integer|between:1,10',
            'name' => 'required|string|max:255',
            'color' => 'nullable|string'
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $priority) {
            $newLevel = $request->level;
            $oldLevel = $priority->level;

            if ($newLevel != $oldLevel) {
                // Shift others
                $others = Priority::where('id', '!=', $priority->id)
                    ->where('level', '>=', $newLevel)
                    ->orderBy('level', 'desc')
                    ->get();
                
                foreach ($others as $p) {
                    $p->increment('level');
                }
            }

            $priority->update($request->all());
        });

        return redirect()->back()->with('success', 'Priority updated and levels shifted.');
    }

    public function destroy($id)
    {
        Priority::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Priority deleted successfully.');
    }
}
