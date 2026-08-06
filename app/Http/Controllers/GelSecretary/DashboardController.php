<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\AnthropicService;
use App\Services\AuditLogService;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $clientIds = $user->userClients()->pluck('client_id')->toArray();
        $query = Client::query();
        if ($user->cabinet_id) {
            $query->where('cabinet_id', $user->cabinet_id)->orWhereIn('id', $clientIds);
        } else {
            $query->whereIn('id', $clientIds);
        }
        $clients = $query->orderBy('nom_entreprise')->get();

        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        $activeClient = $activeClientId ? Client::find($activeClientId) : $clients->first();

        if ($activeClient && !session('active_client_id')) {
            session(['active_client_id' => $activeClient->id]);
        }

        // Tâches
        $tasksQuery = \App\Models\Gel\Task::where('cabinet_id', $user->cabinet_id);
        if ($activeClient) {
            $tasksQuery->where('client_id', $activeClient->id);
        }
        
        $urgentTasks = (clone $tasksQuery)
            ->whereIn('statut', ['a_faire', 'en_cours'])
            ->whereDate('date_echeance', '<=', now()->toDateString())
            ->orderBy('date_echeance', 'asc')
            ->take(5)
            ->get();
            
        $pendingTasksCount = (clone $tasksQuery)->whereIn('statut', ['a_faire', 'en_cours'])->count();
        $completedTasksCount = (clone $tasksQuery)->where('statut', 'termine')->count();
        $overdueTasksCount = (clone $tasksQuery)
            ->whereIn('statut', ['a_faire', 'en_cours'])
            ->whereDate('date_echeance', '<', now()->toDateString())
            ->count();
            
        $completionRate = ($pendingTasksCount + $completedTasksCount > 0) 
            ? round(($completedTasksCount / ($pendingTasksCount + $completedTasksCount)) * 100) 
            : 0;

        // Agenda (RDV du jour & Semaine)
        $agendaQuery = \App\Models\Dae\DaeAgendaEvent::query();
        if ($activeClient) {
            $agendaQuery->where('client_id', $activeClient->id);
        } else {
            $clientIds = $clients->pluck('id');
            $agendaQuery->whereIn('client_id', $clientIds);
        }
        
        $todayEvents = (clone $agendaQuery)
            ->whereDate('start_at', now()->toDateString())
            ->orderBy('start_at', 'asc')
            ->take(5)
            ->get();
            
        $upcomingEvents = (clone $agendaQuery)
            ->where('start_at', '>', now()->endOfDay())
            ->where('start_at', '<=', now()->addDays(7)->endOfDay())
            ->orderBy('start_at', 'asc')
            ->take(5)
            ->get();

        // Documents récents (Aujourd'hui)
        $docsQuery = \App\Models\Document::query();
        if ($activeClient) {
            $docsQuery->where('client_id', $activeClient->id);
        } else {
            $clientIds = $clients->pluck('id');
            $docsQuery->whereIn('client_id', $clientIds);
        }
        $recentDocs = (clone $docsQuery)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $recentDocsCount = (clone $docsQuery)->whereDate('created_at', now()->toDateString())->count();

        // Messages
        $messagesQuery = \App\Models\Gel\GelMessage::where('cabinet_id', $user->cabinet_id)
            ->where('sender_type', 'business');
        if ($activeClient) {
            $messagesQuery->where('client_id', $activeClient->id);
        }
        $unreadMessagesCount = $messagesQuery->where('est_lu', false)->count();

        // RDV pris en ligne récemment (dernières 48h) - tous clients ou client actif
        $newBookingsQuery = \App\Models\Dae\DaeAgendaEvent::where('type', 'rdv')
            ->where('created_at', '>=', now()->subHours(48));
        if ($activeClient) {
            $newBookingsQuery->where('client_id', $activeClient->id);
        } else {
            $clientIds = $clients->pluck('id');
            $newBookingsQuery->whereIn('client_id', $clientIds)->orWhereNull('client_id');
        }
        $newBookings      = $newBookingsQuery->orderBy('created_at', 'desc')->take(5)->get();
        $newBookingsCount = $newBookings->count();

        // ─── Factures arrivant à échéance cette semaine ──────────────────────
        $invoiceClientIds = $clients->pluck('id');
        $invoicesDueThisWeek = \App\Models\Invoice::whereIn('client_id', $invoiceClientIds)
            ->whereDate('due_date', '>=', now()->toDateString())
            ->whereDate('due_date', '<=', now()->addDays(7)->toDateString())
            ->where('balance_due', '>', 0)
            ->where('status', '!=', 'paid')
            ->with('client')
            ->orderBy('due_date', 'asc')
            ->take(5)->get();

        // ─── Déclarations fiscales à échéance cette semaine ────────────────────
        $taxDueThisWeek = \App\Models\AccountingTaxDeclaration::whereIn('client_id', $invoiceClientIds)
            ->whereDate('date_echeance', '>=', now()->toDateString())
            ->whereDate('date_echeance', '<=', now()->addDays(7)->toDateString())
            ->whereIn('status', ['draft', 'pending', 'en_cours'])
            ->with('client')
            ->orderBy('date_echeance', 'asc')
            ->take(5)->get();

        // ─── Temps moyen de traitement des tâches ────────────────────────────
        $avgProcessingMinutes = \App\Models\Gel\Task::where('cabinet_id', $user->cabinet_id)
            ->where('statut', 'termine')
            ->whereNotNull('termine_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, termine_at)) as avg_minutes')
            ->value('avg_minutes');
        $avgProcessingHours = $avgProcessingMinutes ? round($avgProcessingMinutes / 60, 1) : null;

        // ─── Métriques d'action du jour ──────────────────────────────────────────
        $todayTasksCompleted = \App\Models\Gel\Task::where('cabinet_id', $user->cabinet_id)
            ->where('statut', 'termine')
            ->whereDate('termine_at', now()->toDateString())
            ->count();
            
        $todayCalls = \App\Models\ClientCallLog::whereDate('called_at', now()->toDateString())
            ->where('user_id', $user->id)
            ->count();

        $stats = [
            'total_clients'    => $clients->count(),
            'tasks_pending'    => $pendingTasksCount,
            'tasks_completed'  => $completedTasksCount,
            'tasks_overdue'    => $overdueTasksCount,
            'completion_rate'  => $completionRate,
            'messages_unread'  => $unreadMessagesCount,
            'documents_recent' => $recentDocsCount,
            'new_bookings'     => $newBookingsCount,
            'invoices_due_soon' => $invoicesDueThisWeek->count(),
            'tax_due_soon'     => $taxDueThisWeek->count(),
            'avg_processing_hours' => $avgProcessingHours,
            'today_tasks_completed' => $todayTasksCompleted,
            'today_calls' => $todayCalls,
            
            // Nouvelles statistiques premium (Greeting & Productivité) — valeurs RÉELLES uniquement
            'greeting' => [
                'meetings' => $todayEvents->count(),
                'calls' => \App\Models\Gel\Task::where('cabinet_id', $user->cabinet_id)
                    ->whereIn('statut', ['a_faire', 'en_cours'])
                    ->where('titre', 'like', '%appel%')
                    ->count(),
                // Vraies données : courriers non traités + documents à traiter
                'mails' => \App\Models\Dae\DaeCourrier::whereIn('client_id', $clients->pluck('id'))
                    ->whereIn('statut', ['non_traite', 'en_cours', 'recu'])
                    ->count(),
                'validations' => \App\Models\Document::whereIn('client_id', $clients->pluck('id'))
                    ->where('is_archived', false)
                    ->where('workflow_step', 'recu')
                    ->count(),
            ],
            'productivity' => [
                // Temps moyen réel de traitement (en heures) — « — » si aucune donnée
                'time_saved' => $avgProcessingHours,
                'docs_generated' => $recentDocsCount,
                'tasks_completed' => $todayTasksCompleted,
                'messages_sent' => \App\Models\Gel\GelMessage::where('sender_id', $user->id)
                    ->whereDate('created_at', now()->toDateString())
                    ->count(),
            ]
        ];

        // --- IA: Checklist & Anticipation ---
        $aiService = new AnthropicService();
        $cacheKeyChecklist = 'ai_checklist_' . $user->id . '_' . ($activeClient ? $activeClient->id : 'all') . '_' . now()->toDateString();
        
        $aiChecklist = Cache::remember($cacheKeyChecklist, 86400, function() use ($aiService, $urgentTasks, $todayEvents, $unreadMessagesCount, $todayCalls, $activeClient) {
            $prompt = "Génère une checklist de 3 à 5 actions prioritaires pour la journée. ";
            $prompt .= "Contexte : Tâches urgentes: {$urgentTasks->count()}, RDV du jour: {$todayEvents->count()}, Messages non lus: {$unreadMessagesCount}, Appels émis: {$todayCalls}.";
            $system = "Tu es un assistant IA pour un secrétaire. Génère une checklist. Format JSON : [{\"label\": \"Action\", \"type\": \"agenda|tache|message|appel\", \"statut\": false}]";
            
            $result = $aiService->generateJson($prompt, $system);
            
            if ($result && !isset($result['error'])) {
                AuditLogService::log('IA ACTION', clone $activeClient ?? auth()->user(), null, ['action' => 'Génération checklist Ma journée']);
                return $result;
            }
            
            return [
                ['label' => 'Vérifier les messages urgents', 'type' => 'message', 'statut' => false],
                ['label' => 'Préparer les RDV du jour', 'type' => 'agenda', 'statut' => false]
            ];
        });

        // IA: Temps moyen de traitement Commentaire
        $cacheKeyTps = 'ai_tps_comment_' . $user->id;
        $tpsMoyComment = Cache::remember($cacheKeyTps, 86400, function() use ($aiService, $avgProcessingHours) {
            if ($avgProcessingHours === null) return "Pas assez de données pour le moment.";
            $prompt = "Le temps moyen de traitement des tâches est de {$avgProcessingHours} heures. Donne un court commentaire d'encouragement ou d'analyse (1 phrase max).";
            return $aiService->generate($prompt, "Tu es un coach de productivité.");
        });

        // IA: KPI Insights (Analyse intelligente des KPIs)
        $cacheKeyKpis = 'ai_kpi_insights_' . $user->id . '_' . ($activeClient ? $activeClient->id : 'all') . '_' . now()->toDateString();
        $aiKpiInsights = Cache::remember($cacheKeyKpis, 86400, function() use ($aiService, $overdueTasksCount, $invoicesDueThisWeek, $taxDueThisWeek, $unreadMessagesCount) {
            $totalInvoices = $invoicesDueThisWeek->sum('balance_due');
            $prompt = "Génère un texte descriptif (max 2 lignes, très concis et orienté action) pour donner du sens à ces chiffres du tableau de bord :\n";
            $prompt .= "- Tâches en retard : {$overdueTasksCount}\n";
            $prompt .= "- Factures à échéance : {$invoicesDueThisWeek->count()} (Total: {$totalInvoices} euros)\n";
            $prompt .= "- Déclarations fiscales à échéance : {$taxDueThisWeek->count()}\n";
            $prompt .= "- Messages non lus : {$unreadMessagesCount}\n";
            
            $system = "Tu es un assistant IA. Renvoie UNIQUEMENT un objet JSON avec les clés: 'tasks_overdue', 'invoices_due_soon', 'tax_due_soon', 'messages_unread'. Chaque valeur est le texte descriptif demandé (max 2 lignes). Ne renvoie pas de texte en dehors du JSON.";
            
            $result = $aiService->generateJson($prompt, $system);
            
            if ($result && !isset($result['error'])) {
                return $result;
            }
            
            return [
                'tasks_overdue' => "Concentrez-vous sur ces tâches en retard pour assainir la to-do list.",
                'invoices_due_soon' => "Soit {$totalInvoices} € en attente. Des relances sont suggérées.",
                'tax_due_soon' => "Ne ratez pas ces échéances fiscales importantes.",
                'messages_unread' => "Traitez ces messages pour rester à jour avec vos clients."
            ];
        });

        // IA: Anticipation (7 jours)
        foreach ($upcomingEvents as $event) {
            $event->ai_prep = Cache::remember('ai_prep_event_' . $event->id, 86400, function() use ($aiService, $event) {
                $prompt = "RDV: {$event->title} le {$event->start_at}. Résume brièvement la préparation nécessaire en 1 phrase courte.";
                return $aiService->generate($prompt, "Assistant pro");
            });
        }

        // ─── S1 : Documents à traiter (workflow_step=recu) + urgents ────────────
        $docFlowQuery = \App\Models\Document::query();
        if ($activeClient) {
            $docFlowQuery->where('client_id', $activeClient->id);
        } else {
            $docFlowQuery->whereIn('client_id', $clients->pluck('id'));
        }
        $docsToProcess = (clone $docFlowQuery)
            ->where('is_archived', false)
            ->where('workflow_step', 'recu')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
        $docsToProcessCount = (clone $docFlowQuery)
            ->where('workflow_step', 'recu')->count();

        // Documents urgents (priority = urgente) en flow
        $urgentDocs = (clone $docFlowQuery)
            ->where('is_archived', false)
            ->where('priority', 'urgente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ─── S1 : Courriers non traités ───────────────────────────────────────────
        $courriersQuery = \App\Models\Dae\DaeCourrier::query();
        if ($activeClient) {
            $courriersQuery->where('client_id', $activeClient->id);
        } else {
            $courriersQuery->whereIn('client_id', $clients->pluck('id'));
        }
        $unprocessedCourriers = (clone $courriersQuery)
            ->whereIn('statut', ['non_traite', 'en_cours', 'recu'])
            ->orderBy('date_courrier', 'desc')
            ->take(5)
            ->get();
        $unprocessedCourriersCount = (clone $courriersQuery)
            ->whereIn('statut', ['non_traite', 'en_cours', 'recu'])->count();

        $viewName = in_array($user->role, ['admin', 'super_admin', 'director', 'dirigeant']) 
            ? 'gel-secretary.dashboard-director' 
            : 'gel-secretary.dashboard';

        // ─── S16 : Variables pour la vue Dirigeant ───────────────────────────
        if ($viewName === 'gel-secretary.dashboard-director') {
            $cabinetId = $user->cabinet_id;
            $clientIds = $clients->pluck('id');
            
            $dirStats = [
                'pending_tasks'        => \App\Models\Gel\Task::whereIn('client_id', $clientIds)->whereIn('statut', ['a_faire', 'en_cours'])->count(),
                'tasks_done'           => \App\Models\Gel\Task::whereIn('client_id', $clientIds)->where('statut', 'termine')->whereMonth('updated_at', now()->month)->count(),
                'tasks_overdue'        => \App\Models\Gel\Task::whereIn('client_id', $clientIds)->whereIn('statut', ['a_faire', 'en_cours'])->whereDate('date_echeance', '<', now())->count(),
                'docs_to_process'      => \App\Models\Document::whereIn('client_id', $clientIds)->where('workflow_step', 'recu')->count(),
                'courriers_unprocessed'=> \App\Models\Dae\DaeCourrier::whereIn('client_id', $clientIds)->whereIn('statut', ['non_traite', 'recu'])->count(),
                'events_today'         => \App\Models\Dae\DaeAgendaEvent::whereIn('client_id', $clientIds)->whereDate('start_at', now())->count(),
            ];

            $dirDocsToProcess = \App\Models\Document::with('client')
                ->whereIn('client_id', $clientIds)
                ->where('workflow_step', 'recu')
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get();

            $dirUpcomingEvents = \App\Models\Dae\DaeAgendaEvent::whereIn('client_id', $clientIds)
                ->where('start_at', '>', now())
                ->where('start_at', '<=', now()->addDays(7))
                ->orderBy('start_at')
                ->take(6)
                ->get();

            // Activité récente : fusion de plusieurs types d'événements
            $recentActivity = collect();
            \App\Models\Document::with('client')->whereIn('client_id', $clientIds)
                ->where('created_at', '>=', now()->subDays(7))->orderBy('created_at','desc')->take(5)->get()
                ->each(fn($d) => $recentActivity->push([
                    'title' => 'Document ajouté : ' . $d->name,
                    'time'  => $d->created_at->diffForHumans(),
                    'icon'  => 'fa-file-alt', 'bg' => '#F5F3FF', 'color' => '#7C3AED',
                ]));
            \App\Models\Gel\Task::whereIn('client_id', $clientIds)
                ->where('statut','termine')->where('updated_at', '>=', now()->subDays(7))->orderBy('updated_at','desc')->take(5)->get()
                ->each(fn($t) => $recentActivity->push([
                    'title' => 'Tâche terminée : ' . $t->titre,
                    'time'  => $t->updated_at->diffForHumans(),
                    'icon'  => 'fa-check-circle', 'bg' => '#ECFDF5', 'color' => '#059669',
                ]));
            $recentActivity = $recentActivity->sortByDesc('time')->take(8)->values();

            return view('gel-secretary.dashboard-director', [
                'user'             => $user,
                'clients'          => $clients,
                'activeClient'     => $activeClient,
                'stats'            => $dirStats,
                'docsToProcess'    => $dirDocsToProcess,
                'upcomingEvents'   => $dirUpcomingEvents,
                'recentActivity'   => $recentActivity,
            ]);
        }

        return view($viewName, compact(
            'user', 'stats', 'clients', 'activeClient',
            'urgentTasks', 'todayEvents', 'upcomingEvents', 'recentDocs', 'newBookings',
            'invoicesDueThisWeek', 'taxDueThisWeek', 'unreadMessagesCount', 'aiChecklist', 'tpsMoyComment', 'aiKpiInsights',
            // S1
            'docsToProcess', 'docsToProcessCount', 'urgentDocs', 'unprocessedCourriers', 'unprocessedCourriersCount'
        ));
    }


    public function switchClient(Request $request)
    {
        $clientId = $request->input('client_id');
        $client = Client::find($clientId);

        if ($client) {
            session(['active_client_id' => $client->id]);
            Auth::user()->update(['active_client_id' => $client->id]);

            \App\Services\AuditLogService::log('client.switch_context', $client, null, [
                'active_client_id' => $client->id,
                'company_name' => $client->nom_entreprise
            ]);
        }

        return redirect()->back()->with('success', 'Contexte entreprise changé : ' . ($client->nom_entreprise ?? ''));
    }

    public function notifications()
    {
        $user = Auth::user();
        
        $unreadMessages = \App\Models\Gel\GelMessage::where('cabinet_id', $user->cabinet_id)
            ->where('sender_type', 'business')
            ->where('est_lu', false)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'type' => 'message',
                    'title' => 'Nouveau message de ' . ($msg->client->nom_entreprise ?? 'Client inconnu'),
                    'description' => \Illuminate\Support\Str::limit($msg->message, 50),
                    'time' => $msg->created_at->diffForHumans(),
                    'icon' => 'fas fa-envelope text-info',
                    'link' => route('gel-secretary.messagerie.index')
                ];
            });

        $urgentTasks = \App\Models\Gel\Task::where('cabinet_id', $user->cabinet_id)
            ->whereIn('statut', ['a_faire', 'en_cours'])
            ->whereDate('date_echeance', '<=', now()->toDateString())
            ->orderBy('date_echeance', 'asc')
            ->take(5)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'type' => 'task',
                    'title' => 'Tâche urgente: ' . $task->titre,
                    'description' => 'Échéance: ' . \Carbon\Carbon::parse($task->date_echeance)->format('d/m/Y'),
                    'time' => $task->created_at->diffForHumans(),
                    'icon' => 'fas fa-exclamation-circle text-danger',
                    'link' => route('gel-secretary.dashboard')
                ];
            });

        $pendingInvitations = \App\Models\Gel\ClientInvitation::with('client')
            ->where('email', $user->email)
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'type' => 'invitation',
                    'title' => 'Nouvelle invitation',
                    'description' => ($inv->client->name ?? 'Une entreprise') . ' souhaite vous ajouter.',
                    'time' => $inv->created_at->diffForHumans(),
                    'icon' => 'fas fa-envelope-open-text text-warning',
                    'link' => route('gel-secretary.invitations.index')
                ];
            });

        // S17 — Demandes clients en attente (file de traitement)
        $pendingDemandes = \App\Models\CompanyRequest::whereIn('status', ['new', 'pending'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'type' => 'demande',
                    'title' => 'Nouvelle demande client : ' . $d->company_name,
                    'description' => ($d->contact_name ?? '—') . ' (' . ($d->email ?? '') . ')',
                    'time' => $d->created_at->diffForHumans(),
                    'icon' => 'fas fa-inbox text-danger',
                    'link' => route('gel-secretary.requests.index')
                ];
            });

        $notifications = collect($unreadMessages)
            ->concat($urgentTasks)
            ->concat($pendingInvitations)
            ->concat($pendingDemandes)
            ->sortByDesc('time')
            ->values()
            ->take(10);

        return response()->json([
            'count' => $unreadMessages->count() + $urgentTasks->count() + $pendingInvitations->count() + $pendingDemandes->count(),
            'demandes_count' => $pendingDemandes->count(),
            'items' => $notifications
        ]);
    }
}
