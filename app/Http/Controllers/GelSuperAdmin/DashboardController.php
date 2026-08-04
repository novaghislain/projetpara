<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use App\Models\Gel\Entreprise;
use App\Models\AuditTrail;
use App\Models\PortalContact;
use App\Models\GelSuperAdmin\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Cabinets actifs (Entreprises)
        $totalEntreprises = Entreprise::count();
        // On considère un cabinet actif s'il a au moins un propriétaire actif
        $activeEntreprises = Entreprise::actif()->count();
        
        $newEntreprisesThisMonth = Entreprise::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // 2. Contacts Inscrits (Espace Client)
        $totalContacts = PortalContact::count();
        $newContactsThisMonth = PortalContact::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // 3. Utilisateurs totaux
        $totalUsers = User::count();
        
        // Répartition par type de compte
        $usersByType = [
            'entreprise' => User::where('account_type', 'entreprise')->orWhereNotNull('entreprise_id')->count(),
            'cabinet' => User::where('account_type', 'cabinet')->orWhereNotNull('cabinet_id')->count(),
            'client' => User::where('account_type', 'client')->orWhere('role', 'client')->count(),
            'super_admin' => User::where('role', 'super_admin')->count(),
        ];

        // 4. MRR (Revenu Mensuel Récurrent)
        // Somme des prix des plans pour les administrateurs d'entreprise avec un abonnement actif
        $activeBusinessAdmins = User::where('account_type', 'entreprise')
            ->where('subscription_status', 'active')
            ->whereNotNull('plan_id')
            ->get();
            
        $mrr = 0;
        foreach ($activeBusinessAdmins as $admin) {
            $plan = SubscriptionPlan::find($admin->plan_id);
            if ($plan) {
                $mrr += $plan->price;
            }
        }

        // Pour la variation, on compare avec le nombre d'abonnés créés avant le début du mois
        $previousMonthAdmins = User::where('account_type', 'entreprise')
            ->where('subscription_status', 'active')
            ->whereNotNull('plan_id')
            ->where('created_at', '<', now()->startOfMonth())
            ->get();
            
        $mrrPreviousMonth = 0;
        foreach ($previousMonthAdmins as $admin) {
            $plan = SubscriptionPlan::find($admin->plan_id);
            if ($plan) {
                $mrrPreviousMonth += $plan->price;
            }
        }

        $mrrVariation = 0;
        if ($mrrPreviousMonth > 0) {
            $mrrVariation = round((($mrr - $mrrPreviousMonth) / $mrrPreviousMonth) * 100);
        }

        // 5. Activité Récente de la Plateforme
        $recentActivities = AuditTrail::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // 6. Statuts Système
        $systemStatus = [
            'database' => $this->checkDatabase(),
            'mailing' => $this->checkMailing(),
            'anthropic' => 'Opérationnel', // Simulé, potentiellement vérifier un appel API simple
            'momo' => 'Opérationnel', // Simulé
            'disk_space' => $this->checkDiskSpace(),
        ];

        // 7. Graphique d'évolution (6 derniers mois)
        $chartData = $this->getChartData();

        return view('gel-super-admin.dashboard.index', compact(
            'totalEntreprises',
            'activeEntreprises',
            'newEntreprisesThisMonth',
            'totalContacts',
            'newContactsThisMonth',
            'totalUsers',
            'usersByType',
            'mrr',
            'mrrVariation',
            'recentActivities',
            'systemStatus',
            'chartData'
        ));
    }

    private function checkDatabase()
    {
        $start = microtime(true);
        try {
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000);
            return ['status' => 'Opérationnel', 'latency' => $latency . ' ms'];
        } catch (\Exception $e) {
            return ['status' => 'Dégradé', 'latency' => '-'];
        }
    }
    
    private function checkMailing()
    {
        // On vérifie si la configuration SMTP est présente
        if (config('mail.mailers.smtp.host')) {
            return 'Opérationnel';
        }
        return 'Non configuré';
    }
    
    private function checkDiskSpace()
    {
        try {
            $free = disk_free_space(base_path());
            $total = disk_total_space(base_path());
            $percent = round(($free / $total) * 100);
            return ['status' => 'OK', 'free' => round($free / 1024 / 1024 / 1024, 2) . ' GB (' . $percent . '%)'];
        } catch (\Exception $e) {
            return ['status' => 'Inconnu', 'free' => 'N/A'];
        }
    }

    private function getChartData()
    {
        $months = [];
        $mrrData = [];
        $entreprisesData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');
            
            // MRR data pour le mois (estimation basée sur la date de création des admins actifs)
            $adminsAtTime = User::where('account_type', 'entreprise')
                ->where('subscription_status', 'active')
                ->whereNotNull('plan_id')
                ->where('created_at', '<=', $date->endOfMonth())
                ->get();
                
            $monthMrr = 0;
            foreach ($adminsAtTime as $admin) {
                $plan = SubscriptionPlan::find($admin->plan_id);
                if ($plan) {
                    $monthMrr += $plan->price;
                }
            }
            $mrrData[] = $monthMrr;

            // Entreprises data
            $monthEntreprises = Entreprise::where('created_at', '<=', $date->endOfMonth())
                ->count();
            $entreprisesData[] = $monthEntreprises;
        }

        return [
            'labels' => $months,
            'mrr' => $mrrData,
            'entreprises' => $entreprisesData,
        ];
    }
}
