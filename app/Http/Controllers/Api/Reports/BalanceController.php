<?php
namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\BalanceReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API de la balance générale.
 *
 * Génère la balance des comptes avec possibilité d'export CSV
 * pour la période et les classes de comptes sélectionnées.
 */
class BalanceController extends Controller
{
    private BalanceReportService $balanceService;

    /**
     * Constructeur avec injection du service de balance.
     */
    public function __construct(BalanceReportService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    /**
     * Génère la balance générale selon la période et les filtres (classe, comptes).
     *
     * @param Request $request La requête HTTP avec les paramètres de génération.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

        $data = $this->balanceService->generate(
            $clientId,
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('class'),
            $request->input('account_ids')
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Exporte la balance au format CSV.
     *
     * @param Request $request La requête HTTP avec la période d'export.
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

        $csv = $this->balanceService->exportCsv(
            $clientId,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="balance_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
