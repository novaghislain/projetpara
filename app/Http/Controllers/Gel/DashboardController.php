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

/**
 * Contrôleur du tableau de bord GEL.
 * Gère l'affichage de la page d'accueil, les redirections selon le rôle
 * et les statistiques du tableau de bord (clients, missions, pôles, revenus).
 */
class DashboardController extends Controller
{
    /**
     * Affiche la page d'accueil publique (landing page).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (view()->exists('landing')) {
            return view('landing', ['bestSellers' => '[]']);
        }
        return redirect()->route('login');
    }

    /**
     * Affiche le tableau de bord (authentifié).
     * Redirige vers le dashboard approprié selon le rôle :
     * - Comptable → GEL Accountant
     * - Company admin/manager → GEL Business
     * - Super Admin / autres → GEL (Vue SPA)
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Comptable → nouveau dashboard GEL Accountant (Blade)
        if ($user && ($user->isComptable() || $user->role === 'comptable')) {
            return redirect()->to(route('gel-accountant.dashboard'));
        }

        // Informaticien → Dashboard Informatique
        if ($user && $user->role === 'informaticien') {
            return redirect()->to(route('gel-informaticien.dashboard'));
        }

        // Communication → Dashboard Communication
        if ($user && $user->role === 'communication') {
            return redirect()->to(route('gel-communication.dashboard'));
        }

        // Secrétaire → Dashboard Secrétariat
        if ($user && (in_array($user->role, ['secretaire', 'secretary']) || $user->role_secretaire)) {
            return redirect()->to(route('gel-secretary.dashboard'));
        }

        // Company admin / manager → GEL Business dashboard (Blade)
        if ($user && in_array($user->role, ['company_admin', 'company_manager', 'company_employee'])) {
            return redirect()->to(route('gel-business.dashboard'));
        }

        // Super Admin → portail de supervision
        if ($user && $user->role === 'super_admin') {
            return redirect()->to(route('gel-super-admin.dashboard'));
        }

        return view('app', ['page' => 'gel-dashboard']);
    }

    /**
     * API: Retourne les statistiques du tableau de bord.
     * Fournit le total des clients, missions, pôles, les clients/missions récents,
     * la répartition par pôle et les revenus mensuels.
     * Filtre les données selon le rôle de l'utilisateur (super admin/director vs pole).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role === 'super_admin';
        $isDirector = $user->role === 'director';

        $clientsQuery = Client::query();
        $missionsQuery = Mission::query();
        $polesQuery = Pole::query();

        // Filtres selon le rôle : restreindre aux données du pôle si non admin
        if (!$isSuperAdmin && !$isDirector) {
            $poleId = $user->pole_id;
            $clientsQuery->whereHas('poles', fn($q) => $q->where('pole_id', $poleId));
            $missionsQuery->where(function ($q) use ($user, $poleId) {
                $q->where('pole_id', $poleId)
                  ->orWhere('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        // 5 clients les plus récents
        $recentClients = (clone $clientsQuery)
            ->select('id', 'company_name', 'email', 'status')
            ->latest()
            ->take(5)
            ->get();

        // 5 missions les plus récentes avec leur client
        $recentMissions = (clone $missionsQuery)
            ->with('client:id,company_name')
            ->select('id', 'title', 'status', 'progress', 'client_id')
            ->latest()
            ->take(5)
            ->get();

        // Répartition des clients par pôle (avec pourcentage calculé)
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

        // Revenus mensuels sur 12 mois (à partir des factures émises non annulées)
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
