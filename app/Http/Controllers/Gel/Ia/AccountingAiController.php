<?php

namespace App\Http\Controllers\Gel\Ia;

use App\Http\Controllers\Controller;
use App\Models\AiSuggestion;
use App\Services\IA\AccountingAiService;
use Illuminate\Http\Request;

class AccountingAiController extends Controller
{
    /**
     * Contrôleur d'intelligence artificielle pour la comptabilité.
     * Fournit des fonctionnalités de catégorisation automatique,
     * détection d'anomalies, suggestions de régularisation,
     * et un système d'apprentissage continu par feedback.
     */

    public function __construct(
        private readonly AccountingAiService $accountingAi
    ) {}

    /**
     * Catégorise une transaction comptable via l'IA.
     *
     * POST /api/ia/accounting/categorize
     *
     * @param Request $request La requête avec le libellé, montant et type de transaction
     * @return \Illuminate\Http\JsonResponse Les suggestions de catégorisation
     */
    public function categorize(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:500',
            'montant' => 'required|numeric',
            'type' => 'nullable|in:charge,produit,autre',
        ]);

        // Appel au service IA pour suggérer un compte comptable
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
     * Détecte les anomalies comptables pour un client sur une période.
     *
     * GET /api/ia/accounting/anomalies
     *
     * @param Request $request La requête avec le client_id et la période optionnelle
     * @return \Illuminate\Http\JsonResponse La liste des anomalies détectées
     */
    public function anomalies(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'debut' => 'nullable|date',
            'fin' => 'nullable|date',
        ]);

        // Détection des anomalies via le service IA
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
     * Obtient des suggestions de régularisation comptable.
     *
     * GET /api/ia/accounting/regularizations
     *
     * @param Request $request La requête avec le client_id et la date de fin
     * @return \Illuminate\Http\JsonResponse Les suggestions de régularisation
     */
    public function regularizations(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date_fin' => 'required|date',
        ]);

        // Suggestions de régularisation via le service IA
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
     * Liste les suggestions IA pour un client avec filtres.
     *
     * GET /api/ia/suggestions
     *
     * @param Request $request La requête avec les filtres (client_id, status, agent)
     * @return \Illuminate\Http\JsonResponse Les suggestions paginées
     */
    public function suggestions(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'nullable|in:pending,approved,rejected',
            'agent' => 'nullable|string',
        ]);

        // Requête filtrée sur les suggestions AI
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
     * Approuve une suggestion IA.
     *
     * POST /api/ia/suggestions/{id}/approve
     *
     * @param AiSuggestion $suggestion La suggestion à approuver (injection de modèle)
     * @param Request $request La requête HTTP
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function approve(AiSuggestion $suggestion, Request $request)
    {
        // Vérification que la suggestion est en attente
        if ($suggestion->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'La suggestion a déjà été traitée.',
            ], 422);
        }

        // Mise à jour du statut et enregistrement de l'approbateur
        $suggestion->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        // Enregistrement du feedback pour l'apprentissage continu
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
     * Rejette une suggestion IA avec motif.
     *
     * POST /api/ia/suggestions/{id}/reject
     *
     * @param AiSuggestion $suggestion La suggestion à rejeter (injection de modèle)
     * @param Request $request La requête HTTP avec le motif de rejet
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function reject(AiSuggestion $suggestion, Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Vérification que la suggestion est en attente
        if ($suggestion->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'La suggestion a déjà été traitée.',
            ], 422);
        }

        // Mise à jour du statut avec le motif de rejet
        $suggestion->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => $request->reason,
        ]);

        // Enregistrement du feedback négatif pour l'apprentissage
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
     * Exécute l'action d'une suggestion approuvée.
     *
     * POST /api/ia/suggestions/{id}/execute
     *
     * @param AiSuggestion $suggestion La suggestion à exécuter
     * @param Request $request La requête HTTP
     * @return \Illuminate\Http\JsonResponse Résultat de l'exécution
     */
    public function execute(AiSuggestion $suggestion, Request $request)
    {
        // Vérification que la suggestion a été approuvée
        if ($suggestion->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'La suggestion doit être approuvée avant exécution.',
            ], 422);
        }

        // Délégation de l'exécution au service IA
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
     * Enregistre un feedback pour corriger une suggestion (apprentissage continu).
     *
     * POST /api/ia/feedback
     *
     * @param Request $request La requête avec le libellé, montant et compte attendu
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function feedback(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:500',
            'montant' => 'required|numeric',
            'type' => 'required|in:charge,produit,autre',
            'expected_account_code' => 'required|string|max:10',
        ]);

        // Obtention de la suggestion AI pour comparaison
        $suggestions = $this->accountingAi->categorizeTransaction(
            $request->libelle,
            $request->montant,
            $request->type
        );

        // Enregistrement du feedback pour améliorer le modèle
        $this->accountingAi->logLearning(
            'feedback',
            [
                'libelle' => $request->libelle,
                'montant' => $request->montant,
                'type' => $request->type,
            ],
            $suggestions,
            [['account_code' => $request->expected_account_code]],
            // Comparaison entre la suggestion et la réponse attendue
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
