<?php

namespace App\Http\Controllers\GelInformaticien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gel\ItTicket;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord technique (santé, charge de support, etc.)
     */
    public function index()
    {
        // 1. KPI de Support
        $openTickets = ItTicket::where('status', 'nouveau')->count();
        $inProgressTickets = ItTicket::where('status', 'en_cours')->count();
        $myTickets = ItTicket::where('assigned_to', auth()->id())
                             ->whereIn('status', ['nouveau', 'en_cours'])
                             ->count();

        // 2. Alertes de sécurité (ex. échecs de connexion récents)
        $securityAlerts = AuditTrail::where('event', 'LOGIN_FAILED')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        // 3. Santé Technique (Simulé pour l'instant)
        $systemHealth = [
            'api_latency' => rand(120, 250) . ' ms',
            'cpu_load' => rand(10, 45) . ' %',
            'db_connections' => rand(15, 60),
            'last_backup' => \App\Models\AuditLog::where('action', 'system_backup_success')->latest()->value('created_at') ?? now()->subHours(12),
        ];

        return view('gel-informaticien.dashboard.index', compact(
            'openTickets', 
            'inProgressTickets', 
            'myTickets', 
            'securityAlerts', 
            'systemHealth'
        ));
    }
}
