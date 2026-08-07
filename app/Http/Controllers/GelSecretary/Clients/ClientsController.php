<?php

namespace App\Http\Controllers\GelSecretary\Clients;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\ClientCallLog;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientsController extends Controller
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
        $activeClient = Client::find(session('active_client_id') ?? $user->active_client_id);
        
        // Add stats to each client
        foreach ($clients as $client) {
            $client->stats = [
                'tasks_count' => \App\Models\Gel\Task::where('client_id', $client->id)->count(),
                'documents_count' => \App\Models\Document::where('client_id', $client->id)->count(),
                'courriers_count' => \App\Models\Document::where('client_id', $client->id)->where('tags', 'like', '%courrier%')->count() ?: (\App\Models\Dae\DaeCourrier::where('client_id', $client->id)->count() ?? 0),
                'last_activity' => $client->updated_at ? $client->updated_at->diffForHumans() : 'Jamais'
            ];
        }

        return view('gel-secretary.clients.index', compact('clients', 'activeClient'));
    }

    public function show($clientId)
    {
        $client = Client::findOrFail($clientId);
        session(['active_client_id' => $client->id]);

        \App\Services\AuditLogService::log('client.show', $client, null, null);

        $clientIds = Auth::user()->userClients()->pluck('client_id')->toArray();
        $query = Client::query();
        if (Auth::user()->cabinet_id) {
            $query->where('cabinet_id', Auth::user()->cabinet_id)->orWhereIn('id', $clientIds);
        } else {
            $query->whereIn('id', $clientIds);
        }
        $clients = $query->orderBy('nom_entreprise')->get();
        $activeClient = $client;

        // ─── Documents ──────────────────────────────────────────────────────
        $documents = \App\Models\Document::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')->get();

        // ─── Tâches ─────────────────────────────────────────────────────────
        $tasks = \App\Models\Gel\Task::where('client_id', $client->id)
            ->orderBy('date_echeance', 'asc')->get();

        // ─── Agenda ─────────────────────────────────────────────────────────
        $events = \App\Models\Dae\DaeAgendaEvent::where('client_id', $client->id)
            ->orderBy('start_at', 'desc')->get();

        // ─── Messages internes (simples) ─────────────────────────────────────
        $messages = \App\Models\Gel\GelMessage::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')->get();

        // ─── Contacts entreprise ─────────────────────────────────────────────
        $contacts = $client->contacts ?? collect([]);

        // ─── Factures ────────────────────────────────────────────────────────
        $invoices = Invoice::where('client_id', $client->id)
            ->orderBy('invoice_date', 'desc')
            ->take(20)->get();

        // ─── Courriers (DAE) ─────────────────────────────────────────────────
        $courriers = \App\Models\Dae\DaeCourrier::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')->take(20)->get();

        // ─── S2 : Équipe affectée (pivot user_clients) ────────────────────────
        $equipe = \App\Models\UserClient::where('client_id', $client->id)
            ->where('is_active', true)
            ->with(['user:id,name,email,role', 'inviter:id,name'])
            ->orderBy('role')->get();

        // ─── S2 : Rapports de synthèse ────────────────────────────────────────
        $reports = collect([
            [
                'titre' => 'Synthèse du dossier',
                'desc'  => 'Vue globale de l\'activité (tâches, docs, courriers, factures).',
                'icon'  => 'fa-chart-pie',
                'color' => '#6366F1',
            ],
            [
                'titre' => 'Échéances à venir',
                'desc'  => 'Factures impayées et déclarations fiscales des 30 prochains jours.',
                'icon'  => 'fa-calendar-alt',
                'color' => '#F59E0B',
            ],
            [
                'titre' => 'Courriers du mois',
                'desc'  => 'Courriers entrants / sortants traités ce mois-ci.',
                'icon'  => 'fa-envelope-open-text',
                'color' => '#2563EB',
            ],
            [
                'titre' => 'Productivité secrétariat',
                'desc'  => 'Tâches terminées, appels, RDV et documents produits.',
                'icon'  => 'fa-rocket',
                'color' => '#10B981',
            ],
        ]);

        // ─── Journal d'appels ─────────────────────────────────────────────────
        $callLogs = ClientCallLog::where('client_id', $client->id)
            ->with('user')
            ->orderBy('called_at', 'desc')->take(20)->get();

        // ─── Historique des actions ───────────────────────────────────────────
        $history = \App\Models\AuditLog::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')->take(30)->get();

        // ─── S4.3 — Fil d'activité commun Secrétaire ↔ Comptable ──────────────
        $coordinationActivity = \App\Services\CoordinationService::feedForClient($client->id, 15);

        // ─── Santé client ─────────────────────────────────────────────────────
        $overdueTasksCount   = $tasks->whereIn('statut', ['a_faire', 'en_cours'])
            ->filter(fn($t) => $t->date_echeance && \Carbon\Carbon::parse($t->date_echeance)->isPast())
            ->count();
        $unreadMessagesCount = $messages->where('sender_type', 'business')->where('est_lu', false)->count();

        $healthStatus = 'Sain';
        $healthClass  = 'sec-badge-success';
        if ($overdueTasksCount > 0 || $unreadMessagesCount > 0) {
            $healthStatus = 'Attention';
            $healthClass  = 'sec-badge-warning';
        }
        if ($overdueTasksCount > 3) {
            $healthStatus = 'À risque';
            $healthClass  = 'sec-badge-danger';
        }

        // ─── Timeline unifiée (Activité 360°) ───────────────────────────────────
        $timeline = collect();

        foreach ($documents as $doc) {
            $timeline->push([
                'type' => 'document',
                'date' => $doc->created_at,
                'title' => 'Document ajouté : ' . $doc->name,
                'icon' => 'fa-file-pdf',
                'color' => '#EF4444'
            ]);
        }

        foreach ($tasks->whereNotNull('termine_at') as $task) {
            $timeline->push([
                'type' => 'task',
                'date' => $task->termine_at,
                'title' => 'Tâche terminée : ' . $task->titre,
                'icon' => 'fa-check-circle',
                'color' => '#10B981'
            ]);
        }

        foreach ($events->filter(fn($e) => \Carbon\Carbon::parse($e->start_at)->isPast()) as $event) {
            $timeline->push([
                'type' => 'event',
                'date' => $event->start_at,
                'title' => 'Rendez-vous : ' . $event->title,
                'icon' => 'fa-calendar-check',
                'color' => '#8B5CF6'
            ]);
        }

        foreach ($callLogs as $call) {
            $timeline->push([
                'type' => 'call',
                'date' => $call->called_at,
                'title' => 'Appel ' . $call->direction . ' (' . $call->statut . ')',
                'desc' => $call->contact_name,
                'icon' => $call->direction === 'entrant' ? 'fa-phone-volume' : 'fa-phone',
                'color' => $call->direction === 'entrant' ? '#3B82F6' : '#10B981'
            ]);
        }
        
        foreach ($invoices as $inv) {
            $timeline->push([
                'type' => 'invoice',
                'date' => $inv->created_at,
                'title' => 'Facture émise : ' . $inv->invoice_number,
                'icon' => 'fa-file-invoice-dollar',
                'color' => '#F59E0B'
            ]);
        }

        $timeline = $timeline->sortByDesc('date');

        return view('gel-secretary.clients.detail', compact(
            'client', 'clients', 'activeClient',
            'documents', 'tasks', 'events', 'messages', 'contacts',
            'invoices', 'courriers', 'callLogs', 'history', 'timeline',
            'healthStatus', 'healthClass', 'overdueTasksCount', 'unreadMessagesCount',
            'equipe', 'reports', 'coordinationActivity'
        ));
    }

    // ─── Journal d'appels : enregistrer un appel ──────────────────────────────
    public function storeCallLog(Request $request, $clientId)
    {
        $request->validate([
            'direction'    => 'required|in:entrant,sortant',
            'contact_name' => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:30',
            'notes'        => 'nullable|string',
            'statut'       => 'required|in:terminé,sans-réponse,à rappeler',
            'called_at'    => 'required|date',
            'duration_minutes' => 'nullable|integer|min:1',
        ]);

        $log = ClientCallLog::create([
            'client_id'        => $clientId,
            'user_id'          => Auth::id(),
            'direction'        => $request->direction,
            'contact_name'     => $request->contact_name,
            'phone'            => $request->phone,
            'notes'            => $request->notes,
            'statut'           => $request->statut,
            'called_at'        => $request->called_at,
            'duration_minutes' => $request->duration_minutes,
        ]);
        
        $msg = 'Appel enregistré dans le journal.';

        if ($request->has('create_task') && $request->create_task) {
            \App\Models\Gel\Task::create([
                'client_id'   => $clientId,
                'created_by'  => Auth::id(),
                'titre'       => 'Suite à l\'appel avec ' . ($request->contact_name ?: 'le client'),
                'description' => $request->notes,
                'statut'      => 'a_faire',
                'priorite'    => 'moyenne',
                'date_echeance' => now()->addDays(1)->format('Y-m-d'),
            ]);
            
            $log->update(['action_created' => 'task']);
            $msg .= ' Une tâche a été générée automatiquement.';
        }

        return redirect()->route('gel-secretary.clients.show', $clientId)
            ->with('success', $msg);
    }

    // ─── Résumé IA du dossier ────────────────────────────────────────────────
    public function generateAiSummary(Request $request, $clientId)
    {
        $client = Client::findOrFail($clientId);
        
        $tasks = \App\Models\Gel\Task::where('client_id', $clientId)->orderBy('created_at', 'desc')->take(5)->get();
        $courriers = \App\Models\Dae\DaeCourrier::where('client_id', $clientId)->orderBy('created_at', 'desc')->take(3)->get();
        
        $prompt = "Voici les dernières tâches du client {$client->nom_entreprise} :\n";
        foreach($tasks as $t) $prompt .= "- {$t->titre} (Statut: {$t->statut})\n";
        
        $prompt .= "\nEt les derniers courriers :\n";
        foreach($courriers as $c) $prompt .= "- {$c->objet} ({$c->statut})\n";
        
        $prompt .= "\nGénère une synthèse globale de l'activité du dossier en 3 phrases maximum, sur un ton professionnel.";
        
        $aiService = new \App\Services\AnthropicService();
        $summary = $aiService->generate($prompt, "Tu es un(e) assistant(e) administratif(ve) expert(e).");
        
        \App\Services\AuditLogService::log('IA ACTION', $client, null, ['action' => 'Résumé 360 Client']);
        
        return response()->json(['summary' => $summary]);
    }
}
