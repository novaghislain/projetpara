<?php
namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\BalanceSheetService;
use App\Services\Reports\IncomeStatementService;
use App\Services\Reports\CashFlowStatementService;
use App\Services\Reports\TrialBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialStatementsController extends Controller
{
    private BalanceSheetService $balanceSheetService;
    private IncomeStatementService $incomeStatementService;
    private CashFlowStatementService $cashFlowService;
    private TrialBalanceService $trialBalanceService;

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
     * Bilan comptable (Actif / Passif)
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
     * Compte de résultat (P&L)
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
     * Soldes Intermédiaires de Gestion (SIG)
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
     * Tableau de flux de trésorerie
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
     * Balance de vérification (débits = crédits)
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
     * Balance âgée clients/fournisseurs
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
