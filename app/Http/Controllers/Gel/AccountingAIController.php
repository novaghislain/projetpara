<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\AiSuggestion;
use App\Models\AuditTrail;
use App\Services\IA\AccountingAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Contrôleur pour les fonctionnalités d'intelligence artificielle comptable.
 * Gère l'analyse et la catégorisation des transactions, la détection d'anomalies,
 * les suggestions de comptes SYSCOHADA et les prévisions financières
 * via le service AccountingAiService.
 */
class AccountingAIController extends Controller
{
    /** @var AccountingAiService Service d'IA comptable pour les opérations métier */
    protected AccountingAiService $accountingAi;

    /**
     * Initialise le contrôleur avec le service d'IA comptable.
     *
     * @param AccountingAiService $accountingAi Service d'IA pour les opérations comptables
     */
    public function __construct(AccountingAiService $accountingAi)
    {
        $this->accountingAi = $accountingAi;
        $this->middleware('permission:ia.consulter');
        $this->middleware('module:comptabilite');
    }

    /**
     * Affiche le tableau de bord de l'IA comptable.
     * Récupère les 20 dernières suggestions et les statistiques mises en cache.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupération des 20 dernières suggestions de l'agent OHADA
        $suggestions = AiSuggestion::where('agent', 'ohada')
            ->latest()
            ->take(20)
            ->get();

        // Statistiques mises en cache pour 5 minutes (300 secondes)
        $stats = Cache::remember('ai_accounting_stats', 300, function () {
            return [
                'total_suggestions' => AiSuggestion::where('agent', 'ohada')->count(),
                'pending_suggestions' => AiSuggestion::where('agent', 'ohada')->pending()->count(),
                'approved_suggestions' => AiSuggestion::where('agent', 'ohada')->where('status', 'approved')->count(),
                'anomalies_count' => 0,
            ];
        });

        return view('gel.ia.accounting', compact('suggestions', 'stats'));
    }

    /**
     * Analyse et suggère une catégorisation d'écriture.
     * Valide les données entrantes, appelle le service d'IA pour catégoriser,
     * enregistre la suggestion et crée une trace d'audit.
     *
     * @param Request $request La requête HTTP contenant libellé, montant, type et client_id
     * @return \Illuminate\Http\JsonResponse Réponse JSON avec les suggestions de catégorisation
     */
    public function analyzeEntry(Request $request)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:500',
            'montant' => 'required|numeric',
            'type' => 'required|in:charge,produit,tresorerie',
            'client_id' => 'required|integer|exists:clients,id',
        ]);

        // Appel au service d'IA pour catégoriser la transaction
        $suggestions = $this->accountingAi->categorizeTransaction(
            $validated['libelle'],
            $validated['montant'],
            $validated['type']
        );

        // Enregistrer la suggestion d'analyse via le service d'IA
        $this->accountingAi->createSuggestion(
            $validated['client_id'],
            Auth::id(),
            [
                'type' => 'categorization',
                'title' => 'Catégorisation : ' . substr($validated['libelle'], 0, 80),
                'message' => 'Transaction analysée : ' . $validated['libelle'],
                'data' => $suggestions,
                'priority' => 'normal',
                'confidence' => $suggestions[0]['confidence'] ?? 50,
                'action_type' => 'categorize_transaction',
                'action_payload' => [
                    'libelle' => $validated['libelle'],
                    'montant' => $validated['montant'],
                    'type' => $validated['type'],
                    'suggestions' => $suggestions,
                ],
            ]
        );

        // Enregistrer une trace d'audit pour l'analyse effectuée par l'IA
        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'ia_accounting_categorize',
            'auditable_type' => 'App\Models\Client',
            'auditable_id' => $validated['client_id'],
            'description' => 'Analyse IA transaction : ' . substr($validated['libelle'], 0, 100),
        ]);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Suggère des comptes SYSCOHADA pour un libellé donné.
     * Utilise le service d'IA pour proposer une classification comptable pertinente.
     *
     * @param Request $request La requête HTTP contenant le libellé et le client_id
     * @return \Illuminate\Http\JsonResponse Réponse JSON avec les suggestions de comptes
     */
    public function suggestAccounts(Request $request)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:500',
            'client_id' => 'required|integer|exists:clients,id',
        ]);

        // Appel au service d'IA pour obtenir des suggestions de comptes (montant 0 par défaut, type charge)
        $suggestions = $this->accountingAi->categorizeTransaction(
            $validated['libelle'],
            0,
            'charge'
        );

        return response()->json([
            'success' => true,
            'accounts' => $suggestions,
        ]);
    }

    /**
     * Détecte les anomalies comptables sur une période donnée.
     * Utilise le service d'IA pour analyser les écritures et identifier
     * les incohérences ou irrégularités potentielles.
     *
     * @param Request $request La requête HTTP contenant optionnellement client_id, periode_debut, periode_fin
     * @return \Illuminate\Http\JsonResponse Réponse JSON avec la liste des anomalies détectées
     */
    public function detectAnomalies(Request $request)
    {
        // Déterminer le client_id : depuis la requête ou depuis l'utilisateur connecté
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        $periodeDebut = $request->get('periode_debut');
        $periodeFin = $request->get('periode_fin');

        // Détection des anomalies via le service d'IA
        $anomalies = $this->accountingAi->detectAnomalies($clientId, $periodeDebut, $periodeFin);

        return response()->json([
            'success' => true,
            'anomalies' => $anomalies,
            'count' => count($anomalies),
        ]);
    }

    /**
     * Génère automatiquement une écriture comptable.
     * Analyse le libellé et le montant via l'IA, puis crée une suggestion d'écriture
     * avec les comptes SYSCOHADA proposés.
     *
     * @param Request $request La requête HTTP contenant client_id, libellé, montant et type
     * @return \Illuminate\Http\JsonResponse Réponse JSON avec la suggestion d'écriture générée
     */
    public function generateEntry(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'libelle' => 'required|string|max:500',
            'montant' => 'required|numeric',
            'type' => 'required|in:charge,produit,tresorerie',
        ]);

        // Analyse de la transaction par l'IA pour obtenir les lignes d'écriture suggérées
        $suggestions = $this->accountingAi->categorizeTransaction(
            $validated['libelle'],
            $validated['montant'],
            $validated['type']
        );

        // Créer une suggestion d'écriture
        $suggestion = $this->accountingAi->createSuggestion(
            $validated['client_id'],
            Auth::id(),
            [
                'type' => 'entry_generation',
                'title' => 'Génération d\'écriture : ' . substr($validated['libelle'], 0, 80),
                'message' => 'Proposition d\'écriture comptable générée par IA',
                'data' => $suggestions,
                'priority' => 'normal',
                'confidence' => $suggestions[0]['confidence'] ?? 50,
                'action_type' => 'categorize_transaction',
                'action_payload' => [
                    'libelle' => $validated['libelle'],
                    'montant' => $validated['montant'],
                    'type' => $validated['type'],
                    'lines' => $suggestions,
                ],
            ]
        );

        return response()->json([
            'success' => true,
            'suggestion' => $suggestion,
            'accounts' => $suggestions,
        ]);
    }

    /**
     * Prédictions et prévisions financières basées sur les données comptables.
     * Suggère des régularisations automatiques pour le client concerné.
     *
     * @param Request $request La requête HTTP contenant optionnellement le client_id
     * @return \Illuminate\Http\JsonResponse Réponse JSON avec les propositions de régularisation
     */
    public function forecast(Request $request)
    {
        // Déterminer le client_id : depuis la requête ou depuis l'utilisateur connecté
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        // Obtenir les suggestions de régularisation via le service d'IA
        $regularizations = $this->accountingAi->suggestRegularizations($clientId, now()->format('Y-m-d'));

        return response()->json([
            'success' => true,
            'regularizations' => $regularizations,
            'count' => count($regularizations),
        ]);
    }
}
