<?php
namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\BalanceSheetService;
use App\Services\Reports\IncomeStatementService;
use App\Services\Reports\CashFlowStatementService;
use App\Services\Reports\TrialBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API des états financiers.
 *
 * Regroupe les services de génération du bilan (Actif/Passif),
 * du compte de résultat (P&L), des Soldes Intermédiaires de Gestion (SIG),
 * du tableau de flux de trésorerie, de la balance de vérification
 * et de la balance âgée clients/fournisseurs.
 */
class FinancialStatementsController extends Controller
{
    private BalanceSheetService $balanceSheetService;
    private IncomeStatementService $incomeStatementService;
    private CashFlowStatementService $cashFlowService;
    private TrialBalanceService $trialBalanceService;

    /**
     * Constructeur avec injection des services d'états financiers.
     *
     * @param BalanceSheetService $balanceSheetService
     * @param IncomeStatementService $incomeStatementService
     * @param CashFlowStatementService $cashFlowService
     * @param TrialBalanceService $trialBalanceService
     */
    public function __construct(
        BalanceSheetService $balanceSheetService,
        IncomeStatementService $incomeStatementService,
        CashFlowStatementService $cashFlowService,
        TrialBalanceService $trialBalanceService
    ) {
        $this->balanceSheetService = $balanceSheetService;
        $this->incomeStatementService = $incomeStatementService;
        $this->cashFlowService = $cashFlowService;
        $this->trialBalanceService = $trialBalanceService;
    }

    /**
     * Génère le bilan comptable (Actif / Passif) à une date donnée.
     *
     * @param Request $request La requête HTTP avec la date d'arrêté.
     * @return \Illuminate\Http\JsonResponse
     */
    public function balanceSheet(Request $request)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

        $data = $this->balanceSheetService->generate(
            $clientId,
            $request->input('as_of_date')
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Génère le compte de résultat (P&L) sur une période donnée.
     *
     * @param Request $request La requête HTTP avec la période.
     * @return \Illuminate\Http\JsonResponse
     */
    public function incomeStatement(Request $request)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

        $data = $this->incomeStatementService->generate(
            $clientId,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Génère les Soldes Intermédiaires de Gestion (SIG) sur une période.
     *
     * @param Request $request La requête HTTP avec la période.
     * @return \Illuminate\Http\JsonResponse
     */
    public function sig(Request $request)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

        $data = $this->incomeStatementService->generateSIG(
            $clientId,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Génère le tableau de flux de trésorerie sur une période.
     *
     * @param Request $request La requête HTTP avec la période.
     * @return \Illuminate\Http\JsonResponse
     */
    public function cashFlow(Request $request)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

        $data = $this->cashFlowService->generate(
            $clientId,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Génère la balance de vérification (égalité débits = crédits) à une date donnée.
     *
     * @param Request $request La requête HTTP avec la date d'arrêté.
     * @return \Illuminate\Http\JsonResponse
     */
    public function trialBalance(Request $request)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

        $data = $this->trialBalanceService->generate(
            $clientId,
            $request->input('as_of_date')
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Génère la balance âgée (clients ou fournisseurs) à une date donnée.
     *
     * @param Request $request La requête HTTP avec le type (customer/supplier) et la date.
     * @return \Illuminate\Http\JsonResponse
     */
    public function aging(Request $request)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

        $data = $this->trialBalanceService->generateAging(
            $clientId,
            $request->input('type', 'customer'),
            $request->input('as_of_date')
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
