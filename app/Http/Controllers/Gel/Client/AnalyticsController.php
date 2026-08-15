<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Analytics\ReportService;

class AnalyticsController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function generateFinancialReport(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $report = $this->reportService->generateFinancialReport(
            $request->client_id,
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'status' => 'success',
            'data' => $report,
            'message' => 'Rapport financier généré avec succès.'
        ]);
    }
}
