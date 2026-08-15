<?php

namespace App\Http\Controllers\Gel\Direction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Accounting\ConsolidationService;

class ConsolidationController extends Controller
{
    protected ConsolidationService $consolidationService;

    public function __construct(ConsolidationService $consolidationService)
    {
        $this->consolidationService = $consolidationService;
    }

    public function getConsolidatedIncomeStatement(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'uuid'
        ]);

        try {
            $report = $this->consolidationService->consolidateIncomeStatement($request->year, $request->client_ids);
            
            return response()->json([
                'status' => 'success',
                'data' => $report,
                'message' => 'Compte de Résultat consolidé généré avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getConsolidatedBalanceSheet(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'uuid'
        ]);

        try {
            $report = $this->consolidationService->consolidateBalanceSheet($request->year, $request->client_ids);
            
            return response()->json([
                'status' => 'success',
                'data' => $report,
                'message' => 'Bilan Consolidé généré avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
