<?php

namespace App\Http\Controllers\Gel\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\CashFlowForecast;
use App\Services\Accounting\CashFlowService;

class CashFlowController extends Controller
{
    protected CashFlowService $cashFlowService;

    public function __construct(CashFlowService $cashFlowService)
    {
        $this->cashFlowService = $cashFlowService;
    }

    public function addForecast(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0',
            'expected_date' => 'required|date',
        ]);

        $forecast = CashFlowForecast::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $forecast,
            'message' => 'Prévision de trésorerie ajoutée.'
        ]);
    }

    public function getForecasts(Request $request, $clientId)
    {
        $targetDate = $request->query('target_date', now()->addMonths(3)->toDateString());
        $currentBalance = $request->query('current_balance', 0);

        $forecastBalance = $this->cashFlowService->calculateForecastBalance($clientId, $targetDate, $currentBalance);
        $monthlyForecasts = $this->cashFlowService->getMonthlyForecasts($clientId);

        return response()->json([
            'status' => 'success',
            'current_balance' => $currentBalance,
            'forecast_balance_at' => $targetDate,
            'forecast_balance_amount' => $forecastBalance,
            'monthly_forecasts' => $monthlyForecasts
        ]);
    }
}
