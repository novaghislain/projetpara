<?php

namespace App\Http\Controllers\Gel\Ia;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FiscalYear;
use App\Services\IA\FinanceAiService;
use Illuminate\Http\Request;

class FinanceAiController extends Controller
{
    public function __construct(
        private readonly FinanceAiService $financeAi
    ) {}

    /**
     * Résoudre l'exercice actif
     */
    private function resolveFiscalYear(Client $client, ?int $fiscalYearId): ?int
    {
        if ($fiscalYearId) return $fiscalYearId;

        return FiscalYear::where('client_id', $client->id)
            ->where('status', 'open')
            ->value('id');
    }

    /**
     * Obtenir les ratios financiers
     * GET /api/ia/finance/ratios/{client}
     */
    public function ratios(Client $client, Request $request)
    {
        $fiscalYearId = $this->resolveFiscalYear($client, $request->integer('fiscal_year_id'));

        if (!$fiscalYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun exercice ouvert trouvé.',
            ], 404);
        }

        $ratios = $this->financeAi->calculateRatios($client->id, $fiscalYearId);

        return response()->json([
            'success' => true,
            'ratios' => $ratios,
        ]);
    }

    /**
     * Obtenir l'analyse textuelle structurée
     * GET /api/ia/finance/analysis/{client}
     */
    public function analysis(Client $client, Request $request)
    {
        $fiscalYearId = $this->resolveFiscalYear($client, $request->integer('fiscal_year_id'));

        if (!$fiscalYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun exercice ouvert trouvé.',
            ], 404);
        }

        $analysis = $this->financeAi->generateFinancialAnalysis($client->id, $fiscalYearId);

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Prévisions de trésorerie
     * GET /api/ia/finance/cash-flow/{client}
     */
    public function cashFlow(Client $client, Request $request)
    {
        $days = min($request->integer('days', 90), 180);

        $predictions = $this->financeAi->predictCashFlow($client->id, $days);

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    /**
     * Alertes financières
     * GET /api/ia/finance/alerts/{client}
     */
    public function alerts(Client $client, Request $request)
    {
        $fiscalYearId = $this->resolveFiscalYear($client, $request->integer('fiscal_year_id'));

        $alerts = $fiscalYearId
            ? $this->financeAi->detectAlerts($client->id, $fiscalYearId)
            : [];

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'total' => count($alerts),
        ]);
    }

    /**
     * Tableau de bord financier complet
     * GET /api/ia/finance/dashboard/{client}
     */
    public function dashboard(Client $client, Request $request)
    {
        $fiscalYearId = $this->resolveFiscalYear($client, $request->integer('fiscal_year_id'));

        if (!$fiscalYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun exercice ouvert trouvé.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'raison_sociale' => $client->company_name,
            ],
            'ratios' => $this->financeAi->calculateRatios($client->id, $fiscalYearId),
            'analysis' => $this->financeAi->generateFinancialAnalysis($client->id, $fiscalYearId),
            'cash_flow' => $this->financeAi->predictCashFlow($client->id, 90),
            'alerts' => $this->financeAi->detectAlerts($client->id, $fiscalYearId),
        ]);
    }
}
