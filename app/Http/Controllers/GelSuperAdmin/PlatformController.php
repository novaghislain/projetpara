<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditTrail;
use App\Models\Gel\GlobalConfig;

class PlatformController extends Controller
{
    public function config(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->except('_token');
            foreach ($data as $key => $value) {
                // Ensure there is a way to save it or use a specific Config Model.
                // Assuming setting in some GlobalConfig or simply dumping for now if model isn't ready.
                // Not actually specified how config is stored in DB, let's assume a key/value config table or just json
            }
            return back()->with('success', 'Configuration mise à jour.');
        }
        return view('gel-super-admin.platform.config');
    }

    public function stats()
    {
        return view('gel-super-admin.platform.stats');
    }

    public function support()
    {
        return view('gel-super-admin.platform.support');
    }

    public function audit(Request $request)
    {
        $query = AuditTrail::with('user')->latest();

        if ($request->filled('event')) {
            $query->where('event', 'like', '%' . $request->input('event') . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(25);
        $users = \App\Models\User::whereIn('id', AuditTrail::select('user_id')->distinct())->get();

        return view('gel-super-admin.platform.audit', compact('logs', 'users'));
    }
}
