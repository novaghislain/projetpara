<?php

namespace App\Http\Controllers\Cpa;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Affiche le dashboard Crescendo CPA.
     */
    public function index()
    {
        return view('app', [
            'page' => 'cpa-dashboard',
        ]);
    }

    /**
     * API: Retourne les stats pour le dashboard CPA.
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'role' => $user->role,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_company_admin' => $user->is_company_admin,
                'client_id' => $user->client_id,
            ],
        ];

        if ($user->isSuperAdmin()) {
            $stats['clients_count'] = \App\Models\Client::count();
            $stats['users_count'] = \App\Models\User::count();
            $stats['active_licenses'] = \App\Models\License::where('status', 'active')->count();
            $stats['recent_clients'] = \App\Models\Client::latest()->take(5)->get(['id', 'company_name', 'status', 'city', 'created_at']);
        }

        return response()->json($stats);
    }
}
