<?php

namespace App\Http\Controllers\GelAdmin;

use App\Http\Controllers\Controller;
use App\Models\Gel\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AuditLogController extends Controller
{
    /**
     * Display a paginated list of audit logs with optional filters.
     */
    public function index(Request $request)
    {
        $query = AuditLog::query();

        // Apply filters if provided
        if ($request->filled('user_id')) {
            $query->where('actor_id', $request->input('user_id'));
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->input('action')}%");
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->input('entity_type'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25)->appends($request->query());

        return view('gel-admin.audit-logs.index', compact('logs'));
    }

    /**
     * Export the filtered audit logs as a CSV file.
     */
    public function export(Request $request)
    {
        $query = AuditLog::query();
        // Same filters as index()
        if ($request->filled('user_id')) {
            $query->where('actor_id', $request->input('user_id'));
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->input('action')}%");
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->input('entity_type'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $filename = 'audit_logs_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            // CSV header
            fputcsv($handle, [
                'id', 'cabinet_id', 'actor_id', 'actor_name', 'actor_email', 'actor_role',
                'client_id', 'action', 'entity_type', 'entity_id', 'description',
                'old_values', 'new_values', 'ip_address', 'user_agent', 'session_id', 'created_at'
            ]);
            // Chunked export
            $query->orderBy('created_at', 'desc')->chunk(200, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->cabinet_id,
                        $log->actor_id,
                        $log->actor_name,
                        $log->actor_email,
                        $log->actor_role,
                        $log->client_id,
                        $log->action,
                        $log->entity_type,
                        $log->entity_id,
                        $log->description,
                        json_encode($log->old_values),
                        json_encode($log->new_values),
                        $log->ip_address,
                        $log->user_agent,
                        $log->session_id,
                        $log->created_at,
                    ]);
                }
            });
            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}

?>
