<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display activity and security audit trail.
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $action = $request->input('action');

        $query = ActivityLog::with('user');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($action) {
            $query->where('action', $action);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $actions = ActivityLog::distinct()->pluck('action');

        return view('superadmin.activity_logs.index', compact('logs', 'actions', 'search', 'action'));
    }
}
