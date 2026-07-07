<?php

namespace App\Services\Ai;

use App\Models\AiSuggestion;
use App\Models\ErpInvoice;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashflowAgentService
{
    /**
     * Prévisions de trésorerie basées sur l'historique.
     */
    public function forecast(int $clientId, int $months = 3): array
    {
        $now = now();

        // Revenus moyens mensuels (sur 6 derniers mois)
        $revenues = ErpInvoice::where('client_id', $clientId)
            ->whereIn('status', ['paid', 'sent'])
            ->where('created_at', '>=', $now->copy()->subMonths(6))
            ->select(DB::raw('SUM(total_ttc) as total'), DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $avgRevenue = count($revenues) > 0 ? array_sum($revenues) / count($revenues) : 0;

        // Dépenses moyennes mensuelles
        $expenses = AccountingJournalLine::whereHas('journal', function ($q) use ($clientId) {
            $q->where('client_id', $clientId)
              ->where('created_at', '>=', now()->subMonths(6));
        })
        ->where('debit', '>', 0)
        ->select(DB::raw('SUM(debit) as total'), DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"))
        ->groupBy('month')
        ->pluck('total', 'month')
        ->toArray();

        $avgExpense = count($expenses) > 0 ? array_sum($expenses) / count($expenses) : 0;

        // Créances clients (factures émises non payées)
        $receivables = ErpInvoice::where('client_id', $clientId)
            ->whereIn('status', ['sent', 'partial'])
            ->sum('total_ttc');

        // Dettes fournisseurs (factures fournisseurs non payées)
        $payables = ErpInvoice::where('client_id', $clientId)
            ->where('type', 'purchase')
            ->whereIn('status', ['sent', 'partial'])
            ->sum('total_ttc');

        // Solde de trésorerie estimé
        $cashBalance = AccountingJournalLine::whereHas('journal', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })
        ->whereHas('account', fn($q) => $q->whereIn('code', ['512', '531', '571']))
        ->select(DB::raw('SUM(debit) - SUM(credit) as balance'))
        ->value('balance') ?? 0;

        // Prévisions mensuelles
        $projections = [];
        for ($i = 1; $i <= $months; $i++) {
            $month = $now->copy()->addMonths($i);
            $projectedRevenue = $avgRevenue * (1 + ($i * 0.01)); // +1%/mois tendance
            $projectedExpense = $avgExpense * (1 + ($i * 0.005));
            $projectedBalance = $cashBalance + ($projectedRevenue - $projectedExpense) * $i;

            $projections[] = [
                'month' => $month->format('Y-m'),
                'projected_revenue' => round($projectedRevenue, 2),
                'projected_expense' => round($projectedExpense, 2),
                'projected_balance' => round($projectedBalance, 2),
            ];
        }

        // Seuils d'alerte
        $alerts = [];
        $lowBalanceMonths = array_filter($projections, fn($p) => $p['projected_balance'] < 500000);
        if (count($lowBalanceMonths)) {
            $alerts[] = [
                'type' => 'low_balance',
                'severity' => 'warning',
                'title' => 'Trésorerie faible prévue',
                'description' => 'Solde projeté sous 500 000 FCFA dans ' . count($lowBalanceMonths) . ' mois.',
                'months' => array_column($lowBalanceMonths, 'month'),
            ];
        }

        if ($receivables > $avgRevenue * 3) {
            $alerts[] = [
                'type' => 'high_receivables',
                'severity' => 'warning',
                'title' => 'Créances clients élevées',
                'description' => 'Les créances (' . number_format($receivables, 0, ',', ' ') . ' FCFA) représentent ' .
                    round($receivables / max($avgRevenue, 1)) . ' mois de CA.',
            ];
        }

        // Créer suggestion IA
        $suggestion = AiSuggestion::create([
            'client_id' => $clientId,
            'agent' => 'cashflow',
            'type' => 'cashflow_forecast',
            'title' => 'Prévision trésorerie — ' . $months . ' mois',
            'description' => 'Solde actuel: ' . number_format($cashBalance, 0, ',', ' ') . ' FCFA | ' .
                'Moyenne revenus: ' . number_format($avgRevenue, 0, ',', ' ') . ' FCFA/mois | ' .
                'Moyenne dépenses: ' . number_format($avgExpense, 0, ',', ' ') . ' FCFA/mois',
            'data' => [
                'cash_balance' => $cashBalance,
                'avg_revenue' => $avgRevenue,
                'avg_expense' => $avgExpense,
                'receivables' => $receivables,
                'payables' => $payables,
                'projections' => $projections,
                'alerts' => $alerts,
            ],
            'metadata' => ['agent' => 'Cashflow', 'version' => '1.0'],
            'status' => 'pending',
        ]);

        return [
            'suggestion_id' => $suggestion->id,
            'cash_balance' => $cashBalance,
            'avg_revenue' => $avgRevenue,
            'avg_expense' => $avgExpense,
            'receivables' => $receivables,
            'payables' => $payables,
            'projections' => $projections,
            'alerts' => $alerts,
        ];
    }

    public function summary(int $clientId): array
    {
        $pending = AiSuggestion::byClient($clientId)->byAgent('cashflow')->pending()->count();
        $balance = AccountingJournalLine::whereHas('journal', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })
        ->whereHas('account', fn($q) => $q->whereIn('code', ['512', '531', '571']))
        ->select(DB::raw('SUM(debit) - SUM(credit) as balance'))
        ->value('balance') ?? 0;

        return [
            'pending_suggestions' => $pending,
            'estimated_balance' => round($balance, 0),
            'status' => $balance < 500000 ? 'attention' : 'ok',
        ];
    }
}
