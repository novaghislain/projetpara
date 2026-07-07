<?php

namespace App\Http\Controllers\Gel\Ia;

use App\Http\Controllers\Controller;
use App\Models\AiSuggestion;
use App\Services\IA\AccountingAiService;
use Illuminate\Http\Request;

class AccountingAiController extends Controller
{
    public function __construct(
        private readonly AccountingAiService $accountingAi
    ) {}

    /**
     * Catégoriser une transaction manuellement
     * POST /api/ia/accounting/categorize
     */
    public function categorize(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:500',
            'montant' => 'required|numeric',
            'type' => 'nullable|in:charge,produit,autre',
        ]);

        $suggestions = $this->accountingAi->categorizeTransaction(
            $request->libelle,
            $request->montant,
            $request->type ?? 'charge'
        );

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Détecter les anomalies pour un client
     * GET /api/ia/accounting/anomalies
     */
    public function anomalies(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'debut' => 'nullable|date',
            'fin' => 'nullable|date',
        ]);

        $anomalies = $this->accountingAi->detectAnomalies(
            $request->client_id,
            $request->debut,
            $request->fin
        );

        return response()->json([
            'success' => true,
            'anomalies' => $anomalies,
            'total' => count($anomalies),
        ]);
    }

    /**
     * Obtenir les suggestions de régularisation
     * GET /api/ia/accounting/regularizations
     */
    public function regularizations(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date_fin' => 'required|date',
        ]);

        $suggestions = $this->accountingAi->suggestRegularizations(
            $request->client_id,
            $request->date_fin
        );

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Liste des suggestions AI pour un client
     * GET /api/ia/suggestions
     */
    public function suggestions(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'nullable|in:pending,approved,rejected',
            'agent' => 'nullable|string',
        ]);

        $query = AiSuggestion::byClient($request->client_id)
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->agent, fn($q, $v) => $q->byAgent($v))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'suggestions' => $query,
        ]);
    }

    /**
     * Approuver une suggestion
     * POST /api/ia/suggestions/{id}/approve
     */
    public function approve(AiSuggestion $suggestion, Request $request)
    {
        if ($suggestion->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'La suggestion a déjà été traitée.',
            ], 422);
        }

        $suggestion->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        $this->accountingAi->logLearning(
            'approve',
            ['suggestion_id' => $suggestion->id],
            $suggestion->data ?? [],
            null,
            true,
            $suggestion->client_id,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Suggestion approuvée.',
        ]);
    }

    /**
     * Rejeter une suggestion
     * POST /api/ia/suggestions/{id}/reject
     */
    public function reject(AiSuggestion $suggestion, Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if ($suggestion->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'La suggestion a déjà été traitée.',
            ], 422);
        }

        $suggestion->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => $request->reason,
        ]);

        $this->accountingAi->logLearning(
            'reject',
            ['suggestion_id' => $suggestion->id, 'reason' => $request->reason],
            $suggestion->data ?? [],
            null,
            false,
            $suggestion->client_id,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Suggestion rejetée.',
        ]);
    }

    /**
     * Exécuter l'action d'une suggestion approuvée
     * POST /api/ia/suggestions/{id}/execute
     */
    public function execute(AiSuggestion $suggestion, Request $request)
    {
        if ($suggestion->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'La suggestion doit être approuvée avant exécution.',
            ], 422);
        }

        $success = $this->accountingAi->executeApprovedAction(
            $suggestion,
            $request->user()->id
        );

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'Action exécutée avec succès.'
                : 'Échec de l\'exécution.',
        ]);
    }

    /**
     * Feedback : corriger une suggestion (apprentissage continu)
     * POST /api/ia/feedback
     */
    public function feedback(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:500',
            'montant' => 'required|numeric',
            'type' => 'required|in:charge,produit,autre',
            'expected_account_code' => 'required|string|max:10',
        ]);

        // Obtenir la suggestion AI
        $suggestions = $this->accountingAi->categorizeTransaction(
            $request->libelle,
            $request->montant,
            $request->type
        );

        // Enregistrer le feedback
        $this->accountingAi->logLearning(
            'feedback',
            [
                'libelle' => $request->libelle,
                'montant' => $request->montant,
                'type' => $request->type,
            ],
            $suggestions,
            [['account_code' => $request->expected_account_code]],
            $suggestions[0]['account_code'] === $request->expected_account_code,
            $request->user()->client_id,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Feedback enregistré. Merci d\'améliorer l\'IA !',
        ]);
    }
}
