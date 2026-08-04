<?php

namespace App\Http\Controllers\Gel\Ia;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FiscalYear;
use App\Services\IA\FinanceAiService;
use Illuminate\Http\Request;

class FinanceAiController extends Controller
{
    /**
     * Contrôleur d'intelligence artificielle pour l'analyse financière.
     * Expose les endpoints de calcul de ratios, d'analyse textuelle,
     * de prévisions de trésorerie, d'alertes financières et un tableau
     * de bord financier complet pour un client.
     */

    public function __construct(
        private readonly FinanceAiService $financeAi
    ) {}

    /**
     * Résout l'identifiant de l'exercice fiscal : utilise celui fourni
     * ou récupère le dernier exercice ouvert pour le client.
     *
     * @param Client $client Le client propriétaire de l'exercice
     * @param int|null $fiscalYearId L'identifiant fourni (optionnel)
     * @return int|null L'identifiant de l'exercice résolu
     */
    private function resolveFiscalYear(Client $client, ?int $fiscalYearId): ?int
    {
        // Retourne l'ID fourni s'il existe, sinon cherche un exercice ouvert
        if ($fiscalYearId) return $fiscalYearId;

        return FiscalYear::where('client_id', $client->id)
            ->where('status', 'open')
            ->value('id');
    }

    /**
     * Calcule les ratios financiers d'un client.
     *
     * GET /api/ia/finance/ratios/{client}
     *
     * @param Client $client Le client à analyser
     * @param Request $request La requête avec l'exercice fiscal optionnel
     * @return \Illuminate\Http\JsonResponse Les ratios financiers
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
     * Génère une analyse textuelle structurée de la situation financière.
     *
     * GET /api/ia/finance/analysis/{client}
     *
     * @param Client $client Le client à analyser
     * @param Request $request La requête avec l'exercice fiscal optionnel
     * @return \Illuminate\Http\JsonResponse L'analyse textuelle
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
     * Prédit les flux de trésorerie futurs sur une période donnée.
     *
     * GET /api/ia/finance/cash-flow/{client}
     *
     * @param Client $client Le client à analyser
     * @param Request $request La requête avec le nombre de jours (max 180)
     * @return \Illuminate\Http\JsonResponse Les prévisions de trésorerie
     */
    public function cashFlow(Client $client, Request $request)
    {
        // Limitation à 180 jours maximum pour la prévision
        $days = min($request->integer('days', 90), 180);

        $predictions = $this->financeAi->predictCashFlow($client->id, $days);

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    /**
     * Détecte les alertes financières et anomalies.
     *
     * GET /api/ia/finance/alerts/{client}
     *
     * @param Client $client Le client à analyser
     * @param Request $request La requête avec l'exercice fiscal optionnel
     * @return \Illuminate\Http\JsonResponse Les alertes financières
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
     * Tableau de bord financier complet : ratios, analyse, prévisions et alertes.
     *
     * GET /api/ia/finance/dashboard/{client}
     *
     * @param Client $client Le client à analyser
     * @param Request $request La requête avec l'exercice fiscal optionnel
     * @return \Illuminate\Http\JsonResponse Le tableau de bord complet
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

        // Agrégation de toutes les analyses financières disponibles
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
