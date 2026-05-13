<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Priority;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SchedulingController extends Controller
{
    protected $schedulingService;

    public function __construct(\App\Services\SchedulingService $schedulingService)
    {
        $this->schedulingService = $schedulingService;
    }

    public function index()
    {
        // Tuning: Fetch only necessary columns for performance
        $workOrders = WorkOrder::with([
                'priority:id,name,color,level', 
                'customer:id,name', 
                'products.item:id,name,code', 
                'products.item.substitutes:id,name,code',
                'stages.machine:id,name',
                'stages.machine.substitutes:id,name',
                'stages.machine.capabilities:id,name',
                'stages.items.item:id,name,code',
                'stages.items.item.substitutes:id,name,code'
            ])
            ->leftJoin('priorities', 'work_orders.priority_id', '=', 'priorities.id')
            ->select([
                'work_orders.id', 'work_orders.wo_number', 'work_orders.production_line', 
                'work_orders.status', 'work_orders.priority_id', 'work_orders.customer_id',
                'work_orders.scheduled_start', 'work_orders.scheduled_end', 'work_orders.sort_order'
            ])
            ->whereIn('work_orders.status', ['ready_to_production', 'in_progress'])
            ->orderBy('work_orders.production_line', 'asc')
            ->orderByRaw('CASE WHEN priorities.level IS NULL THEN 999 ELSE priorities.level END ASC')
            ->orderByRaw('CASE WHEN work_orders.sort_order = 0 OR work_orders.sort_order IS NULL THEN 9999 ELSE work_orders.sort_order END ASC')
            ->orderBy('work_orders.updated_at', 'desc')
            ->get();
            
        $priorities = Priority::select('id', 'name', 'color', 'level')->get();
        
        return view('production.scheduling.index', compact('workOrders', 'priorities'));
    }

    public function updateSchedule(Request $request, $id)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'priority_id' => 'nullable|exists:priorities,id',
            'production_line' => 'required|integer',
            'sort_order' => 'nullable|integer',
            'stage_machines' => 'nullable|array'
        ]);

        try {
            $this->schedulingService->updateWorkOrderSchedule($id, $request->all());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function repairSchedules()
    {
        $lines = WorkOrder::whereNotNull('production_line')->distinct()->pluck('production_line');
        foreach($lines as $line) {
            $this->schedulingService->resequenceLine($line);
        }
        return response()->json(['success' => true, 'message' => 'All schedules repaired successfully']);
    }

    public function getSubstitutes(Request $request)
    {
        $type = $request->type;
        $id = $request->id;

        if ($type === 'machine') {
            $machine = \App\Models\Machine::find($id);
            if (!$machine) return response()->json([]);
            return response()->json($machine->substitutes);
        }

        if ($type === 'item') {
            $item = \App\Models\Item::find($id);
            if (!$item) return response()->json([]);
            return response()->json($item->substitutes);
        }

        return response()->json([]);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'schedules' => 'required|array',
            'schedules.*.id' => 'required|exists:work_orders,id',
            'schedules.*.start' => 'required|date',
            'schedules.*.end' => 'required|date',
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function() use ($request) {
                $linesAffected = [];
                foreach ($request->schedules as $sched) {
                    $wo = WorkOrder::findOrFail($sched['id']);
                    $wo->update([
                        'scheduled_start' => $sched['start'],
                        'scheduled_end' => $sched['end'],
                    ]);
                    $linesAffected[] = $wo->production_line;
                }

                // Resequence all affected lines
                foreach (array_unique($linesAffected) as $line) {
                    $this->schedulingService->resequenceLine($line);
                }

                return response()->json(['success' => true, 'message' => 'Schedules re-sequenced successfully']);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
