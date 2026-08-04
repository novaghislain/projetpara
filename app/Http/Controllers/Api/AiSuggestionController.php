<?php

namespace App\Http\Controllers\Api;

use App\Models\AiSuggestion;
use App\Models\AiLearningLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API des suggestions IA.
 *
 * Gère l'affichage, l'approbation, le rejet des suggestions générées
 * par les agents IA, ainsi que le journal d'apprentissage associé.
 */
class AiSuggestionController extends BaseApiController
{
    /**
     * Liste des suggestions IA pour l'entreprise connectée.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $clientId = $this->getClientId();

        $query = AiSuggestion::byClient($clientId)
            ->with('user:id,name')
            ->latest();

        // Filtres optionnels
        if ($request->filled('agent')) {
            $query->byAgent($request->input('agent'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->boolean('unread')) {
            $query->unread();
        }

        $suggestions = $query->paginate($request->input('per_page', 20));

        return response()->json($suggestions);
    }

    /**
     * Détail d'une suggestion (la marque comme lue).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $clientId = $this->getClientId();
        $suggestion = AiSuggestion::byClient($clientId)
            ->with(['user:id,name', 'approver:id,name'])
            ->findOrFail($id);

        // Marquer comme lue
        if (!$suggestion->read_at) {
            $suggestion->update(['read_at' => now()]);
        }

        return response()->json($suggestion);
    }

    /**
     * Approuver une suggestion en attente et journaliser l'apprentissage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, int $id)
    {
        $clientId = $this->getClientId();
        $suggestion = AiSuggestion::byClient($clientId)->pending()->findOrFail($id);

        $suggestion->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Journal d'apprentissage
        AiLearningLog::create([
            'client_id' => $clientId,
            'agent' => $suggestion->agent,
            'type' => 'suggestion_approved',
            'input_data' => $suggestion->description,
            'output_data' => json_encode($suggestion->data),
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Suggestion approuvée', 'suggestion' => $suggestion]);
    }

    /**
     * Rejeter une suggestion en attente et journaliser l'apprentissage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, int $id)
    {
        $clientId = $this->getClientId();
        $suggestion = AiSuggestion::byClient($clientId)->pending()->findOrFail($id);

        $request->validate(['reason' => 'required|string|max:1000']);

        $suggestion->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('reason'),
            'approved_by' => Auth::id(),  // Celui qui a pris la décision
            'approved_at' => now(),
        ]);

        // Journal d'apprentissage
        AiLearningLog::create([
            'client_id' => $clientId,
            'agent' => $suggestion->agent,
            'type' => 'suggestion_rejected',
            'input_data' => $suggestion->description,
            'output_data' => json_encode($suggestion->data),
            'correction' => $request->input('reason'),
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Suggestion rejetée', 'suggestion' => $suggestion]);
    }

    /**
     * Marquer une suggestion comme lue.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markRead(int $id)
    {
        $clientId = $this->getClientId();
        $suggestion = AiSuggestion::byClient($clientId)->findOrFail($id);
        $suggestion->update(['read_at' => now()]);

        return response()->json(['message' => 'Marquée comme lue']);
    }

    /**
     * Marquer toutes les suggestions non lues comme lues pour l'entreprise.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllRead()
    {
        $clientId = $this->getClientId();
        AiSuggestion::byClient($clientId)->unread()->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes marquées comme lues']);
    }

    /**
     * Compter les suggestions non lues pour l'entreprise connectée.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount()
    {
        $clientId = $this->getClientId();
        $count = AiSuggestion::byClient($clientId)->unread()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Supprimer une suggestion.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        $clientId = $this->getClientId();
        $suggestion = AiSuggestion::byClient($clientId)->findOrFail($id);
        $suggestion->delete();

        return response()->json(['message' => 'Suggestion supprimée']);
    }

    /**
     * Journal d'apprentissage (lecture seule) des actions IA.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function learningLog(Request $request)
    {
        $clientId = $this->getClientId();

        $query = AiLearningLog::byClient($clientId)
            ->with('user:id,name')
            ->latest();

        if ($request->filled('agent')) {
            $query->byAgent($request->input('agent'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $logs = $query->paginate($request->input('per_page', 20));

        return response()->json($logs);
    }
}
