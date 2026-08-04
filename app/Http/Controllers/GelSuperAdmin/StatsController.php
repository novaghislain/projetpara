<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use App\Models\Gel\Entreprise;
use App\Models\PortalContact;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', '30'); // jours
        $startDate = now()->subDays($period);

        // Acquisition
        $newEntreprises = Entreprise::where('created_at', '>=', $startDate)->count();
        $newContacts = PortalContact::where('created_at', '>=', $startDate)->count();
        
        // Utilisateurs par type créés dans la période
        $newUsers = User::where('created_at', '>=', $startDate)->count();

        // Répartition par formule d'abonnement (simulé avec User account_type = entreprise)
        $plansDistribution = User::where('account_type', 'entreprise')
            ->whereNotNull('plan_id')
            ->selectRaw('plan_id, count(*) as total')
            ->groupBy('plan_id')
            ->get();
            
        // Pour éviter une jointure complexe, on charge les noms des plans
        $planNames = \App\Models\GelSuperAdmin\SubscriptionPlan::pluck('name', 'id')->toArray();
        foreach ($plansDistribution as $dist) {
            $dist->plan_name = $planNames[$dist->plan_id] ?? 'Plan inconnu';
        }

        // Évolution des inscriptions sur la période
        $inscriptionsLabels = [];
        $inscriptionsData = [];
        
        $interval = $period <= 30 ? 'day' : 'month';
        
        if ($interval === 'day') {
            for ($i = $period; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $inscriptionsLabels[] = $date->format('d/m');
                $inscriptionsData[] = Entreprise::whereDate('created_at', $date)->count();
            }
        } else {
            $months = ceil($period / 30);
            for ($i = $months; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $inscriptionsLabels[] = $date->translatedFormat('M Y');
                $inscriptionsData[] = Entreprise::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)->count();
            }
        }

        return view('gel-super-admin.stats.index', compact(
            'period',
            'newEntreprises',
            'newContacts',
            'newUsers',
            'plansDistribution',
            'inscriptionsLabels',
            'inscriptionsData'
        ));
    }
}
