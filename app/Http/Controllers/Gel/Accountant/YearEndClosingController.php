<?php

namespace App\Http\Controllers\Gel\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Accounting\FinancialReportService;

class YearEndClosingController extends Controller
{
    protected FinancialReportService $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function getIncomeStatement(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'client_id' => 'required|uuid'
        ]);

        try {
            $report = $this->reportService->generateIncomeStatement($request->year, $request->client_id);
            return response()->json([
                'status' => 'success',
                'data' => $report,
                'message' => 'Compte de Résultat généré avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getBalanceSheet(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'client_id' => 'required|uuid'
        ]);

        try {
            $report = $this->reportService->generateBalanceSheet($request->year, $request->client_id);
            return response()->json([
                'status' => 'success',
                'data' => $report,
                'message' => 'Bilan OHADA généré avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
