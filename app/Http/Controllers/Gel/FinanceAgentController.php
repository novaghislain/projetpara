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
    protected FinanceAiService $financeAi;

    public function __construct(FinanceAiService $financeAi)
    {
        $this->financeAi = $financeAi;
        $this->middleware('permission:ia.consulter');
    }

    /**
     * Affiche le tableau de bord financier IA.
     */
    public function index()
    {
        $alerts = AiSuggestion::where('agent', 'finance')
            ->latest()
            ->take(10)
            ->get();

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
     * Prévisions de trésorerie.
     */
    public function cashFlowForecast(Request $request)
    {
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        $days = $request->integer('days', 90);
        $forecast = $this->financeAi->predictCashFlow($clientId, $days);

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
     * Analyse de rentabilité.
     */
    public function profitability(Request $request)
    {
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        $fiscalYearId = $request->get('fiscal_year_id');

        if (!$fiscalYearId) {
            // Prendre le dernier exercice fiscal
            $fiscalYearId = \App\Models\FiscalYear::where('client_id', $clientId)
                ->latest()
                ->value('id');
        }

        if (!$fiscalYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun exercice fiscal trouvé pour ce client.',
            ], 404);
        }

        $ratios = $this->financeAi->calculateRatios($clientId, $fiscalYearId);
        $analysis = $this->financeAi->generateFinancialAnalysis($clientId, $fiscalYearId);

        return response()->json([
            'success' => true,
            'ratios' => $ratios,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Optimisation fiscale.
     */
    public function taxOptimization(Request $request)
    {
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        $fiscalYearId = $request->get('fiscal_year_id');
        if (!$fiscalYearId) {
            $fiscalYearId = \App\Models\FiscalYear::where('client_id', $clientId)
                ->latest()
                ->value('id');
        }

        if (!$fiscalYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun exercice fiscal trouvé.',
            ], 404);
        }

        $analysis = $this->financeAi->generateFinancialAnalysis($clientId, $fiscalYearId);

        // Extraire les recommandations fiscales
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
     * Détection de fraude.
     */
    public function fraudDetection(Request $request)
    {
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        $fiscalYearId = $request->get('fiscal_year_id');
        if (!$fiscalYearId) {
            $fiscalYearId = \App\Models\FiscalYear::where('client_id', $clientId)
                ->latest()
                ->value('id');
        }

        $alerts = [];
        if ($fiscalYearId) {
            $alerts = $this->financeAi->detectAlerts($clientId, $fiscalYearId);
        }

        // Enregistrer les alertes comme suggestions
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
     * Benchmarking (comparaison sectorielle).
     */
    public function benchmarking(Request $request)
    {
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        $fiscalYearId = $request->get('fiscal_year_id');
        if (!$fiscalYearId) {
            $fiscalYearId = \App\Models\FiscalYear::where('client_id', $clientId)
                ->latest()
                ->value('id');
        }

        $benchmarks = [];
        if ($fiscalYearId) {
            $ratios = $this->financeAi->calculateRatios($clientId, $fiscalYearId);
            $benchmarks = [
                'client_ratios' => $ratios,
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
     * Génère un rapport financier.
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|integer|exists:clients,id',
            'fiscal_year_id' => 'nullable|integer|exists:fiscal_years,id',
            'type' => 'required|in:analyse,ratios,complet',
        ]);

        $clientId = $validated['client_id'] ?? Auth::user()->active_client_id ?? Auth::user()->client_id;
        $fiscalYearId = $validated['fiscal_year_id'] ?? \App\Models\FiscalYear::where('client_id', $clientId)->latest()->value('id');

        if (!$fiscalYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun exercice fiscal trouvé.',
            ], 404);
        }

        $report = [
            'type' => $validated['type'],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'client_id' => $clientId,
        ];

        if (in_array($validated['type'], ['analyse', 'complet'])) {
            $report['analysis'] = $this->financeAi->generateFinancialAnalysis($clientId, $fiscalYearId);
        }

        if (in_array($validated['type'], ['ratios', 'complet'])) {
            $report['ratios'] = $this->financeAi->calculateRatios($clientId, $fiscalYearId);
        }

        if ($validated['type'] === 'complet') {
            $report['alerts'] = $this->financeAi->detectAlerts($clientId, $fiscalYearId);
        }

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Alertes en temps réel (dernières alertes non lues).
     */
    public function realtimeAlerts()
    {
        $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;

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
