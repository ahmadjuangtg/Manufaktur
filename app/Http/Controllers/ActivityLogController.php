<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission('security_log_view')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat log aktivitas.');
        }

        $search = $request->search;
        $user_id = $request->user_id;
        $action = $request->action;
        $date_start = $request->date_start;
        $date_end = $request->date_end;

        $logs = ActivityLog::with('user')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('description', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($uq) use ($search) {
                              $uq->where('name', 'like', "%{$search}%");
                          });
                });
            })
            ->when($user_id, function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            })
            ->when($action, function ($q) use ($action) {
                $q->where('action', $action);
            })
            ->when($date_start, function ($q) use ($date_start) {
                $q->where('created_at', '>=', $date_start . ' 00:00:00');
            })
            ->when($date_end, function ($q) use ($date_end) {
                $q->where('created_at', '<=', $date_end . ' 23:59:59');
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $users = User::orderBy('name')->get();

        return view('security.activity_logs.index', compact('logs', 'users', 'search', 'user_id', 'action', 'date_start', 'date_end'));
    }
}
