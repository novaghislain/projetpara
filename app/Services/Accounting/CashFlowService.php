<?php

namespace App\Services\Accounting;

use App\Models\Accounting\CashFlowForecast;
use Carbon\Carbon;

class CashFlowService
{
    /**
     * Calcule le solde de trésorerie prévisionnel à une date donnée
     */
    public function calculateForecastBalance($clientId, $targetDate, $currentBalance = 0)
    {
        $forecasts = CashFlowForecast::where('client_id', $clientId)
            ->where('status', 'pending')
            ->where('expected_date', '<=', $targetDate)
            ->get();

        $totalIn = $forecasts->where('type', 'in')->sum('amount');
        $totalOut = $forecasts->where('type', 'out')->sum('amount');

        return $currentBalance + $totalIn - $totalOut;
    }

    /**
     * Récupère les prévisions groupées par mois
     */
    public function getMonthlyForecasts($clientId, $monthsAhead = 6)
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addMonths($monthsAhead);

        return CashFlowForecast::where('client_id', $clientId)
            ->whereBetween('expected_date', [$startDate, $endDate])
            ->orderBy('expected_date')
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->expected_date)->format('Y-m');
            });
    }
}
