<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Mission;
use App\Models\Pole;
use App\Models\CompanyInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Affiche la page d'accueil publique (landing page).
     */
    public function index()
    {
        return view('landing');
    }

    /**
     * Affiche le tableau de bord (authentifié).
     */
    public function dashboard()
    {
        return view('app', ['page' => 'gel-dashboard']);
    }

    /**
     * API: Retourne les statistiques du tableau de bord.
     */
    public function stats()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role === 'super_admin';
        $isDirector = $user->role === 'director';

        $clientsQuery = Client::query();
        $missionsQuery = Mission::query();
        $polesQuery = Pole::query();

        // Filtres selon le rôle
        if (!$isSuperAdmin && !$isDirector) {
            $poleId = $user->pole_id;
            $clientsQuery->whereHas('poles', fn($q) => $q->where('pole_id', $poleId));
            $missionsQuery->where(function ($q) use ($user, $poleId) {
                $q->where('pole_id', $poleId)
                  ->orWhere('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        // Clients récents
        $recentClients = (clone $clientsQuery)
            ->select('id', 'company_name', 'email', 'status')
            ->latest()
            ->take(5)
            ->get();

        // Missions récentes avec leur client
        $recentMissions = (clone $missionsQuery)
            ->with('client:id,company_name')
            ->select('id', 'title', 'status', 'progress', 'client_id')
            ->latest()
            ->take(5)
            ->get();

        // Répartition par pôle
        $poles = $polesQuery->select('id', 'name', 'color', 'slug')->get();
        $poleDistribution = $poles->map(function ($pole) {
            $clientCount = DB::table('client_pole')->where('pole_id', $pole->id)->count();
            return [
                'name' => $pole->name,
                'color' => $pole->color ?: '#FF7900',
                'count' => $clientCount,
                'pourcentage' => 0, // calculé après
            ];
        });
        $maxCount = $poleDistribution->max('count') ?: 1;
        $poleDistribution = $poleDistribution->map(function ($p) use ($maxCount) {
            $p['pourcentage'] = round(($p['count'] / $maxCount) * 100);
            return $p;
        })->values();

        // Revenus mensuels (factures émises)
        $monthlyRevenue = CompanyInvoice::select(
            DB::raw("DATE_FORMAT(issue_date, '%Y-%m') as month"),
            DB::raw('SUM(total_ttc) as total')
        )
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('issue_date')
            ->groupBy(DB::raw("DATE_FORMAT(issue_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(issue_date, '%Y-%m')"))
            ->take(12)
            ->get()
            ->map(fn($r) => [
                'month' => $r->month,
                'total' => (float) $r->total,
            ]);

        return response()->json([
            'total_clients'      => $clientsQuery->count(),
            'active_clients'     => (clone $clientsQuery)->where('status', 'actif')->count(),
            'total_missions'     => $missionsQuery->count(),
            'pending_missions'   => (clone $missionsQuery)->whereIn('status', ['a_faire', 'en_cours'])->count(),
            'completed_missions' => (clone $missionsQuery)->where('status', 'terminee')->count(),
            'total_poles'        => $polesQuery->count(),

            'recent_clients'   => $recentClients,
            'recent_missions'  => $recentMissions,
            'pole_distribution' => $poleDistribution,
            'monthly_revenue'  => $monthlyRevenue,
        ]);
    }
}
