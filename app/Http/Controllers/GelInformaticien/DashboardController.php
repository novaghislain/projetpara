<?php

namespace App\Http\Controllers\GelInformaticien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gel\ItTicket;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\DB;
use App\Models\Gel\ItDevRequest;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord technique (santé, charge de support, etc.)
     */
    public function index()
    {
        // 1. KPI de Support
        $openTickets = ItTicket::whereIn('status', ['nouveau', 'en_cours'])->count();
        $myTickets = ItTicket::where('assigned_to', auth()->id())
                             ->whereIn('status', ['nouveau', 'en_cours'])
                             ->count();

        // Temps moyen de résolution (heures) sur les 30 derniers jours
        $avgResolutionTime = 0;
        
        try {
            // Tentative PostgreSQL
            $avgResolutionTime = ItTicket::where('status', 'resolu')
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - created_at))/3600) as avg_time')
                ->value('avg_time');
        } catch (\Illuminate\Database\QueryException $e) {
            // Fallback for MySQL/MariaDB or SQLite
            $avgResolutionTime = ItTicket::where('status', 'resolu')
                ->where('created_at', '>=', now()->subDays(30))
                ->get()
                ->map(function ($t) {
                    return $t->updated_at->diffInHours($t->created_at);
                })
                ->avg();
        }
        
        $avgResolutionTime = round($avgResolutionTime ?? 0, 1);

        // 2. Alertes de sécurité (ex. échecs de connexion récents ou accès non autorisés)
        $securityAlerts = AuditTrail::where('event', 'like', '%FAILED%')
            ->orWhere('event', 'like', '%UNAUTHORIZED%')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        // 3. Demandes de dev en attente
        $devRequests = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('it_dev_requests')) {
            $devRequests = DB::table('it_dev_requests')->whereIn('status', ['nouveau', 'devis'])->count();
        }

        // 4. Santé Technique (Simulé pour l'instant)
        $systemHealth = [
            'api_latency' => rand(80, 150) . ' ms',
            'cpu_load' => rand(10, 30) . ' %',
            'db_connections' => rand(15, 60),
            'last_backup' => AuditTrail::where('event', 'backup.success')->latest()->value('created_at') ?? now()->subHours(12),
        ];

        return view('gel-informaticien.dashboard.index', compact(
            'openTickets', 
            'myTickets', 
            'avgResolutionTime',
            'securityAlerts',
            'devRequests',
            'systemHealth'
        ));
    }
}
