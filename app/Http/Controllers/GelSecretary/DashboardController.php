<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Récupérer les clients accessibles par l'utilisateur
        $clientIds = \App\Models\UserClient::where('user_id', $user->id)
            ->pluck('client_id')->toArray();

        $query = Client::query();
        if ($user->cabinet_id) {
            $query->where('cabinet_id', $user->cabinet_id)->orWhereIn('id', $clientIds);
        } elseif (!empty($clientIds)) {
            $query->whereIn('id', $clientIds);
        }

        $clients = $query->orderBy('nom_entreprise')->get();
        $activeClient = Client::find(session('active_client_id'));

        // Statistiques globales
        $stats = [
            'clients_count' => $clients->count(),
            'tasks_pending' => 0,
            'courriers_today' => 0,
            'events_week' => 0,
        ];

        try {
            $stats['tasks_pending'] = \App\Models\Gel\Task::whereIn('client_id', $clients->pluck('id'))
                ->whereIn('statut', ['a_faire', 'en_cours'])->count();
        } catch (\Exception $e) {}

        try {
            $stats['courriers_today'] = \App\Models\Dae\DaeCourrier::whereIn('client_id', $clients->pluck('id'))
                ->whereDate('created_at', today())->count();
        } catch (\Exception $e) {}

        try {
            $stats['events_week'] = \App\Models\Dae\DaeAgendaEvent::whereIn('client_id', $clients->pluck('id'))
                ->whereBetween('start_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        } catch (\Exception $e) {}

        $stats['completion_rate'] = 85;
        $docsToProcessCount = 5;

        // Data for AI Checklist
        $aiChecklist = \DB::table('gel_tasks')
            ->whereIn('client_id', $clients->pluck('id'))
            ->where('statut', '!=', 'termine')
            ->orderBy('priorite', 'desc')
            ->limit(5)
            ->get()
            ->map(function($t) {
                $c = \DB::table('entreprises')->find($t->client_id);
                return [
                    'label' => $t->titre,
                    'client_name' => $c ? $c->nom_entreprise : 'Général',
                    'statut' => false
                ];
            })->toArray();

        // Data for Urgent Clients
        $urgentClients = [];
        foreach($clients as $c) {
            $pendingDocs = \DB::table('dae_documents')->where('client_id', $c->id)->where('statut', 'en_attente')->count();
            $unprocessedCourriers = \DB::table('dae_courriers')->where('client_id', $c->id)->whereIn('statut', ['recu', 'en_cours'])->count();
            
            $health = 100 - ($pendingDocs * 5) - ($unprocessedCourriers * 10);
            $health = max(0, $health);
            
            if ($health < 80 || $pendingDocs > 0 || $unprocessedCourriers > 0) {
                $urgentClients[] = (object)[
                    'company_name' => $c->nom_entreprise,
                    'health' => $health,
                    'pending_docs' => $pendingDocs,
                    'unprocessed_courriers' => $unprocessedCourriers
                ];
            }
        }
        usort($urgentClients, fn($a, $b) => $a->health <=> $b->health);
        $urgentClients = array_slice($urgentClients, 0, 5);

        // Data for Activity Feed
        $activityFeed = \DB::table('gel_audit_logs')
            ->whereIn('client_id', $clients->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($log) {
                return (object)[
                    'color' => '#3B82F6',
                    'icon' => 'fa-info-circle',
                    'text' => $log->action . ($log->details ? ' - ' . substr($log->details, 0, 50) : ''),
                    'time' => \Carbon\Carbon::parse($log->created_at)->diffForHumans()
                ];
            })->toArray();

        if (empty($activityFeed)) {
            $activityFeed = [
                (object)['color' => '#10B981', 'icon' => 'fa-check', 'text' => 'Système prêt et à jour', 'time' => 'Maintenant']
            ];
        }

        return view('gel-secretary.dashboard', compact('clients', 'activeClient', 'stats', 'user', 'docsToProcessCount', 'aiChecklist', 'urgentClients', 'activityFeed'));
    }

    public function switchClient(Request $request)
    {
        $clientId = $request->input('client_id');
        if ($clientId) {
            session(['active_client_id' => $clientId]);
        }
        return redirect()->back()->with('success', 'Client sélectionné.');
    }

    public function switchClientClear(Request $request)
    {
        session()->forget('active_client_id');
        return redirect()->back()->with('success', 'Sélection de client effacée.');
    }
}

