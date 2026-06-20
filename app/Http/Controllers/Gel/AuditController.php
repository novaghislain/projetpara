<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur du journal d'audit.
 */
class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditTrail::with('user');

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('user')) {
            $query->where(function ($q) use ($request) {
                $q->where('user_id', $request->user)
                  ->orWhereHas('user', fn($q) => $q->where('email', 'like', "%{$request->user}%"));
            });
        }
        if ($request->filled('model')) {
            $query->where('auditable_type', 'like', "%{$request->model}%");
        }
        if ($request->filled('ip')) {
            $query->where('ip_address', $request->ip);
        }
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $logs = $query->latest()->paginate(50);

        return view('app', ['page' => 'gel-audit', 'props' => compact('logs')]);
    }
}
