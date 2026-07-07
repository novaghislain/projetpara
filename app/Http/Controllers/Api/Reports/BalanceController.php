<?php
namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\BalanceReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    private BalanceReportService $balanceService;

    public function __construct(BalanceReportService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    /**
     * Balance générale
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
     * Export CSV
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
