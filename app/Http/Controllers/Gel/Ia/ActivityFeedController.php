<?php

namespace App\Http\Controllers\Gel\Ia;

use App\Http\Controllers\Controller;
use App\Models\AiSuggestion;
use Illuminate\Http\Request;

class ActivityFeedController extends Controller
{
    /**
     * Contrôleur du fil d'activité des suggestions IA.
     * Permet de lister, filtrer, approuver, rejeter, exécuter
     * et supprimer les suggestions générées par les agents IA.
     * Fournit également des statistiques pour le tableau de bord.
     */

    /**
     * Liste les suggestions/événements avec filtres avancés.
     *
     * GET /api/ia/feed
     *
     * @param Request $request La requête avec les filtres (agent, type, status, priorite, date, recherche)
     * @return \Illuminate\Http\JsonResponse Les suggestions paginées avec métadonnées
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $clientId = $request->user()->active_client_id;
        $perPage = min((int) $request->input('per_page', 20), 100);

        // Requête de base : suggestions de l'utilisateur pour son client actif
        $query = AiSuggestion::with(['client:id,company_name', 'approver:id,name'])
            ->where('user_id', $userId)
            ->where('client_id', $clientId);

        // Filtre par agent (accounting|ohada|customer|finance|cashflow)
        if ($request->filled('agent')) {
            $query->where('agent', $request->input('agent'));
        }

        // Filtre par type (suggestion|alerte|analyse|relance|opportunite|prediction)
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filtre par statut (pending|approved|rejected|executed)
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtre par priorité (critical|high|normal|low) stockée dans le champ JSON data
        if ($request->filled('priority')) {
            $query->whereRaw(
                'JSON_UNQUOTE(JSON_EXTRACT(data, ?)) = ?',
                ['$.priority', $request->input('priority')]
            );
        }

        // Recherche textuelle dans le titre et la description
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre par plage de dates
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Tri : priorité décroissante (critical en premier) puis date de création
        $query->orderByRaw("
            CASE
                WHEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.priority')) = 'critical' THEN 0
                WHEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.priority')) = 'high'     THEN 1
                WHEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.priority')) = 'normal'   THEN 2
                WHEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.priority')) = 'low'      THEN 3
                ELSE 2
            END
        ")->orderBy('created_at', 'desc');

        $suggestions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'     => $suggestions->items(),
            'meta'     => [
                'current_page' => $suggestions->currentPage(),
                'last_page'    => $suggestions->lastPage(),
                'per_page'     => $suggestions->perPage(),
                'total'        => $suggestions->total(),
            ],
        ]);
    }

    /**
     * Retourne le nombre de suggestions non lues.
     *
     * GET /api/ia/feed/unread-count
     *
     * @param Request $request La requête HTTP
     * @return \Illuminate\Http\JsonResponse Le nombre de suggestions non lues
     */
    public function unreadCount(Request $request)
    {
        $count = AiSuggestion::where('user_id', $request->user()->id)
            ->where('client_id', $request->user()->active_client_id)
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'count'   => $count,
        ]);
    }

    /**
     * Marque une suggestion comme lue.
     *
     * POST /api/ia/feed/{id}/read
     *
     * @param Request $request La requête HTTP
     * @param AiSuggestion $suggestion La suggestion à marquer
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function markAsRead(Request $request, AiSuggestion $suggestion)
    {
        $this->authorizeAccess($request, $suggestion);

        $suggestion->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Suggestion marquée comme lue.',
        ]);
    }

    /**
     * Marque toutes les suggestions comme lues pour l'utilisateur.
     *
     * POST /api/ia/feed/read-all
     *
     * @param Request $request La requête HTTP
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function markAllAsRead(Request $request)
    {
        AiSuggestion::where('user_id', $request->user()->id)
            ->where('client_id', $request->user()->active_client_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Toutes les suggestions marquées comme lues.',
        ]);
    }

    /**
     * Approuve une suggestion.
     *
     * POST /api/ia/feed/{id}/approve
     *
     * @param Request $request La requête HTTP
     * @param AiSuggestion $suggestion La suggestion à approuver
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function approve(Request $request, AiSuggestion $suggestion)
    {
        $this->authorizeAccess($request, $suggestion);

        $suggestion->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'read_at'     => $suggestion->read_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Suggestion approuvée.',
        ]);
    }

    /**
     * Rejette une suggestion avec un motif.
     *
     * POST /api/ia/feed/{id}/reject
     *
     * @param Request $request La requête HTTP avec le motif de rejet
     * @param AiSuggestion $suggestion La suggestion à rejeter
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function reject(Request $request, AiSuggestion $suggestion)
    {
        $this->authorizeAccess($request, $suggestion);

        $request->validate(['reason' => 'required|string|max:500']);

        $suggestion->update([
            'status'           => 'rejected',
            'approved_by'      => $request->user()->id,
            'approved_at'      => now(),
            'rejection_reason' => $request->input('reason'),
            'read_at'          => $suggestion->read_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Suggestion rejetée.',
        ]);
    }

    /**
     * Exécute une suggestion (marque comme exécutée).
     *
     * POST /api/ia/feed/{id}/execute
     *
     * @param Request $request La requête HTTP
     * @param AiSuggestion $suggestion La suggestion à exécuter
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function execute(Request $request, AiSuggestion $suggestion)
    {
        $this->authorizeAccess($request, $suggestion);

        $suggestion->update([
            'status'      => 'executed',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'read_at'     => $suggestion->read_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Suggestion marquée comme exécutée.',
        ]);
    }

    /**
     * Supprime une suggestion.
     *
     * DELETE /api/ia/feed/{id}
     *
     * @param Request $request La requête HTTP
     * @param AiSuggestion $suggestion La suggestion à supprimer
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function destroy(Request $request, AiSuggestion $suggestion)
    {
        $this->authorizeAccess($request, $suggestion);

        $suggestion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Suggestion supprimée.',
        ]);
    }

    /**
     * Statistiques du tableau de bord (par agent, statut, priorité).
     *
     * GET /api/ia/feed/stats
     *
     * @param Request $request La requête HTTP
     * @return \Illuminate\Http\JsonResponse Les statistiques détaillées
     */
    public function stats(Request $request)
    {
        $userId = $request->user()->id;
        $clientId = $request->user()->active_client_id;

        // Requête de base pour les statistiques
        $base = AiSuggestion::where('user_id', $userId)
            ->where('client_id', $clientId);

        // Statistiques par agent IA
        $byAgent = (clone $base)
            ->selectRaw('agent, COUNT(*) as total')
            ->groupBy('agent')
            ->pluck('total', 'agent')
            ->toArray();

        // Statistiques par statut
        $byStatus = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Statistiques par priorité (depuis le champ JSON data)
        $priorities = (clone $base)
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.priority')) as priority, COUNT(*) as total")
            ->whereRaw("JSON_EXTRACT(data, '$.priority') IS NOT NULL")
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();

        return response()->json([
            'success' => true,
            'stats'   => [
                'total'          => (clone $base)->count(),
                'unread'         => (clone $base)->whereNull('read_at')->count(),
                'pending'        => (clone $base)->where('status', 'pending')->count(),
                'approved'       => (clone $base)->where('status', 'approved')->count(),
                'rejected'       => (clone $base)->where('status', 'rejected')->count(),
                'executed'       => (clone $base)->where('status', 'executed')->count(),
                'by_agent'       => $byAgent,
                'by_status'      => $byStatus,
                'by_priority'    => $priorities,
            ],
        ]);
    }

    /**
     * Vérifie que la suggestion appartient bien au client actif de l'utilisateur.
     *
     * @param Request $request La requête HTTP
     * @param AiSuggestion $suggestion La suggestion à vérifier
     * @return void
     */
    private function authorizeAccess(Request $request, AiSuggestion $suggestion): void
    {
        abort_if(
            $suggestion->client_id !== (int) $request->user()->active_client_id,
            403,
            'Cette suggestion ne vous appartient pas.'
        );
    }
}
