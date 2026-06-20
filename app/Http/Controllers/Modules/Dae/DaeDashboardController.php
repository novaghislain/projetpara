<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeCourrier;
use App\Models\Dae\DaeEmail;
use App\Models\Dae\DaeAgendaEvent;
use App\Models\Dae\DaeContrat;
use App\Models\Dae\DaeDocument;
use App\Models\Dae\DaeTache;
use App\Models\Dae\DaeAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DaeDashboardController extends Controller
{
    public function index()
    {
        return view('app', ['page' => 'dae-dashboard']);
    }

    public function stats(Request $request)
    {
        $user = Auth::user();
        $clientIds = $user->isSuperAdmin() ? null : ($user->clients_assignes ?? []);

        $query = fn($model) => $clientIds
            ? $model->whereIn('client_id', $clientIds)
            : $model;

        $stats = [
            'courriers'    => $query(DaeCourrier::query())->count(),
            'courriers_urgents' => $query(DaeCourrier::query())->where('urgence', 'urgent')->where('statut', '!=', 'archive')->count(),
            'emails'        => $query(DaeEmail::query())->count(),
            'emails_non_lus' => $query(DaeEmail::query())->where('statut', 'recu')->count(),
            'evenements'    => $query(DaeAgendaEvent::query())->whereDate('start', '>=', now()->startOfDay())->count(),
            'contrats'      => $query(DaeContrat::query())->count(),
            'contrats_actifs' => $query(DaeContrat::query())->where('statut', 'actif')->count(),
            'documents'     => $query(DaeDocument::query())->count(),
            'taches'        => $query(DaeTache::query())->whereIn('statut', ['a_faire', 'en_cours'])->count(),
            'taches_terminees' => $query(DaeTache::query())->where('statut', 'terminee')->count(),
        ];

        // Activité récente (15 dernières actions)
        $activiteQuery = DaeAuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(15);
        if ($clientIds) {
            $activiteQuery->whereHasMorph('entity', [
                DaeCourrier::class, DaeEmail::class, DaeAgendaEvent::class,
                DaeContrat::class, DaeDocument::class, DaeTache::class,
            ], function ($q) use ($clientIds) {
                $q->whereIn('client_id', $clientIds);
            });
        }
        $activite = $activiteQuery->get()->map(fn($log) => [
            'id'        => $log->id,
            'module'    => $log->dae_module,
            'action'    => $log->action,
            'entity'    => class_basename($log->entity_type),
            'user'      => $log->user?->name ?? 'Système',
            'date'      => $log->created_at->diffForHumans(),
            'created_at' => $log->created_at->toIso8601String(),
        ]);

        // Événements du jour
        $eventsDuJour = $query(DaeAgendaEvent::query())
            ->whereDate('start', now()->today())
            ->orderBy('start')
            ->get()
            ->map(fn($e) => [
                'id'    => $e->id,
                'title' => $e->title,
                'type'  => $e->type,
                'time'  => $e->start->format('H:i'),
                'color' => $e->couleur,
            ]);

        // Alertes
        $alertes = collect();

        // Contrats expirant dans 30 jours
        $expirations = $query(DaeContrat::query())
            ->where('statut', 'actif')
            ->whereDate('date_fin', '<=', now()->addDays(30))
            ->whereDate('date_fin', '>=', now())
            ->get();
        foreach ($expirations as $c) {
            $alertes->push([
                'type'    => 'warning',
                'module'  => 'contrats',
                'message' => "Contrat « {$c->titre} » expire le {$c->date_fin->format('d/m/Y')}",
            ]);
        }

        // Conformité en retard
        $conformiteRetard = $query(\App\Models\Dae\DaeConformite::query())
            ->whereIn('statut', ['a_faire', 'en_cours'])
            ->whereDate('date_limite', '<', now())
            ->get();
        foreach ($conformiteRetard as $c) {
            $alertes->push([
                'type'    => 'danger',
                'module'  => 'conformite',
                'message' => "Conformité « {$c->titre} » en retard (échéance {$c->date_limite->format('d/m/Y')})",
            ]);
        }

        return response()->json([
            'stats'    => $stats,
            'activite' => $activite,
            'evenements' => $eventsDuJour,
            'alertes'  => $alertes,
        ]);
    }
}
