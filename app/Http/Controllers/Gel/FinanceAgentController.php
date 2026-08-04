<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\AiSuggestion;
use App\Models\AuditTrail;
use App\Services\IA\FinanceAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FinanceAgentController extends Controller
{
    /**
     * Contrôleur de l'agent IA financier.
     * Fournit les fonctionnalités d'analyse financière assistée par IA :
     * prévisions de trésorerie, analyse de rentabilité, optimisation fiscale,
     * détection de fraude, benchmarking sectoriel et génération de rapports.
     */

    protected FinanceAiService $financeAi;

    public function __construct(FinanceAiService $financeAi)
    {
        $this->financeAi = $financeAi;
        $this->middleware('permission:ia.consulter');
    }

    /**
     * Affiche le tableau de bord financier IA.
     * Présente les alertes récentes et les indicateurs clés de performance.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupération des 10 dernières alertes financières
        $alerts = AiSuggestion::where('agent', 'finance')
            ->latest()
            ->take(10)
            ->get();

        // Mise en cache des statistiques financières (5 minutes)
        $stats = Cache::remember('ai_finance_stats', 300, function () {
            return [
                'total_analyses' => AiSuggestion::where('agent', 'finance')->count(),
                'pending_alerts' => AiSuggestion::where('agent', 'finance')->pending()->count(),
                'tresorerie' => [
                    'solde' => 0,
                    'solde_formatted' => '0 FCFA',
                ],
                'chiffre_affaires' => [
                    'montant' => 0,
                    'montant_formatted' => '0 FCFA',
                ],
                'rentabilite' => [
                    'resultat' => 0,
                    'resultat_formatted' => '0 FCFA',
                    'marge' => 'N/A',
                ],
                'clients' => [
                    'total_clients' => 0,
                    'impayes' => 0,
                    'impayes_formatted' => '0 FCFA',
                ],
            ];
        });

        return view('gel.ia.finance', compact('alerts', 'stats'));
    }

    /**
     * Génère des prévisions de trésorerie sur une période donnée.
     *
     * @param Request $request La requête HTTP avec les paramètres client_id et days
     * @return \Illuminate\Http\JsonResponse Les prévisions de trésorerie
     */
    public function cashFlowForecast(Request $request)
    {
        // Détermination du client (actif ou spécifié)
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        $days = $request->integer('days', 90);
        // Appel au service IA pour la prédiction
        $forecast = $this->financeAi->predictCashFlow($clientId, $days);

        // Traçage de l'action dans l'audit
        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'ia_cashflow_forecast',
            'auditable_type' => 'App\Models\Client',
            'auditable_id' => $clientId,
            'description' => 'Prévision de trésorerie sur ' . $days . ' jours',
        ]);

        return response()->json([
            'success' => true,
            'forecast' => $forecast,
        ]);
    }

    /**
     * Analyse la rentabilité d'un client sur un exercice fiscal.
     * Calcule les ratios financiers et génère une analyse détaillée.
     *
     * @param Request $request La requête HTTP avec les paramètres client_id et fiscal_year_id
     * @return \Illuminate\Http\JsonResponse Les ratios et l'analyse financière
     */
    public function profitability(Request $request)
    {
        // Détermination du client
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        $fiscalYearId = $request->get('fiscal_year_id');

        // Si aucun exercice fiscal n'est spécifié, on prend le dernier
        if (!$fiscalYearId) {
            $fiscalYearId = \App\Models\FiscalYear::where('client_id', $clientId)
                ->latest()
                ->value('id');
        }

        // Vérification de l'existence d'un exercice fiscal
        if (!$fiscalYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun exercice fiscal trouvé pour ce client.',
            ], 404);
        }

        // Calcul des ratios et génération de l'analyse
        $ratios = $this->financeAi->calculateRatios($clientId, $fiscalYearId);
        $analysis = $this->financeAi->generateFinancialAnalysis($clientId, $fiscalYearId);

        return response()->json([
            'success' => true,
            'ratios' => $ratios,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Optimisation fiscale : analyse et recommandations.
     *
     * @param Request $request La requête HTTP avec les paramètres client_id et fiscal_year_id
     * @return \Illuminate\Http\JsonResponse Les recommandations fiscales
     */
    public function taxOptimization(Request $request)
    {
        // Détermination du client
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        // Récupération de l'exercice fiscal (dernier par défaut)
        $fiscalYearId = $request->get('fiscal_year_id');
        if (!$fiscalYearId) {
            $fiscalYearId = \App\Models\FiscalYear::where('client_id', $clientId)
                ->latest()
                ->value('id');
        }

        // Vérification de l'existence d'un exercice fiscal
        if (!$fiscalYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun exercice fiscal trouvé.',
            ], 404);
        }

        // Analyse financière complète
        $analysis = $this->financeAi->generateFinancialAnalysis($clientId, $fiscalYearId);

        // Extraction des recommandations fiscales depuis l'analyse
        $recommendations = [];
        foreach ($analysis['recommandations'] as $rec) {
            $recommendations[] = [
                'type' => 'fiscal',
                'message' => $rec,
            ];
        }

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Détection de fraude et d'anomalies comptables.
     *
     * @param Request $request La requête HTTP avec les paramètres client_id et fiscal_year_id
     * @return \Illuminate\Http\JsonResponse Les alertes de détection
     */
    public function fraudDetection(Request $request)
    {
        // Détermination du client
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        // Récupération de l'exercice fiscal
        $fiscalYearId = $request->get('fiscal_year_id');
        if (!$fiscalYearId) {
            $fiscalYearId = \App\Models\FiscalYear::where('client_id', $clientId)
                ->latest()
                ->value('id');
        }

        // Détection des anomalies via le service IA
        $alerts = [];
        if ($fiscalYearId) {
            $alerts = $this->financeAi->detectAlerts($clientId, $fiscalYearId);
        }

        // Enregistrement des alertes comme suggestions persistantes
        foreach ($alerts as $alert) {
            AiSuggestion::create([
                'client_id' => $clientId,
                'user_id' => Auth::id(),
                'agent' => 'finance',
                'type' => $alert['type'],
                'title' => $alert['title'],
                'description' => $alert['message'],
                'data' => $alert,
                'metadata' => [
                    'severity' => $alert['severity'],
                    'source' => 'finance_ai',
                ],
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'count' => count($alerts),
        ]);
    }

    /**
     * Benchmarking : comparaison des ratios du client avec les moyennes sectorielles.
     *
     * @param Request $request La requête HTTP avec les paramètres client_id et fiscal_year_id
     * @return \Illuminate\Http\JsonResponse Les benchmarks sectoriels
     */
    public function benchmarking(Request $request)
    {
        // Détermination du client
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        // Récupération de l'exercice fiscal
        $fiscalYearId = $request->get('fiscal_year_id');
        if (!$fiscalYearId) {
            $fiscalYearId = \App\Models\FiscalYear::where('client_id', $clientId)
                ->latest()
                ->value('id');
        }

        // Construction des benchmarks
        $benchmarks = [];
        if ($fiscalYearId) {
            $ratios = $this->financeAi->calculateRatios($clientId, $fiscalYearId);
            $benchmarks = [
                'client_ratios' => $ratios,
                // Moyennes sectorielles de référence (à affiner avec des données réelles)
                'sector_averages' => [
                    'liquidite_generale' => 1.5,
                    'rentabilite_nette' => 8.0,
                    'endettement' => 0.8,
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'benchmarks' => $benchmarks,
        ]);
    }

    /**
     * Génère un rapport financier complet (analyse, ratios, alertes).
     *
     * @param Request $request La requête HTTP avec le type de rapport souhaité
     * @return \Illuminate\Http\JsonResponse Le rapport financier généré
     */
    public function generateReport(Request $request)
    {
        // Validation des paramètres
        $validated = $request->validate([
            'client_id' => 'nullable|integer|exists:clients,id',
            'fiscal_year_id' => 'nullable|integer|exists:fiscal_years,id',
            'type' => 'required|in:analyse,ratios,complet',
        ]);

        // Détermination du client et de l'exercice
        $clientId = $validated['client_id'] ?? Auth::user()->active_client_id ?? Auth::user()->client_id;
        $fiscalYearId = $validated['fiscal_year_id'] ?? \App\Models\FiscalYear::where('client_id', $clientId)->latest()->value('id');

        // Vérification de l'existence d'un exercice fiscal
        if (!$fiscalYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun exercice fiscal trouvé.',
            ], 404);
        }

        // Construction du rapport selon le type demandé
        $report = [
            'type' => $validated['type'],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'client_id' => $clientId,
        ];

        // Ajout de l'analyse financière selon le type
        if (in_array($validated['type'], ['analyse', 'complet'])) {
            $report['analysis'] = $this->financeAi->generateFinancialAnalysis($clientId, $fiscalYearId);
        }

        // Ajout des ratios selon le type
        if (in_array($validated['type'], ['ratios', 'complet'])) {
            $report['ratios'] = $this->financeAi->calculateRatios($clientId, $fiscalYearId);
        }

        // Ajout des alertes pour un rapport complet
        if ($validated['type'] === 'complet') {
            $report['alerts'] = $this->financeAi->detectAlerts($clientId, $fiscalYearId);
        }

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Alertes en temps réel non lues.
     *
     * @return \Illuminate\Http\JsonResponse Les dernières alertes non lues
     */
    public function realtimeAlerts()
    {
        $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;

        // Récupération des 20 dernières alertes en attente
        $alerts = AiSuggestion::where('agent', 'finance')
            ->where('status', 'pending')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'type' => $a->type,
                'title' => $a->title,
                'message' => $a->description,
                'data' => $a->data,
                'created_at' => $a->created_at,
            ]);

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'unread_count' => $alerts->count(),
        ]);
    }
}
