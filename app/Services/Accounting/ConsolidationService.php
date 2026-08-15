<?php

namespace App\Services\Accounting;

class ConsolidationService
{
    protected FinancialReportService $financialReportService;

    public function __construct(FinancialReportService $financialReportService)
    {
        $this->financialReportService = $financialReportService;
    }

    /**
     * Consolide le Compte de Résultat pour un groupe d'entreprises.
     */
    public function consolidateIncomeStatement(int $year, array $clientIds)
    {
        $consolidatedRevenues = 0;
        $consolidatedExpenses = 0;
        $consolidatedNetIncome = 0;
        $combinedDetails = [
            'revenues' => [],
            'expenses' => []
        ];

        foreach ($clientIds as $clientId) {
            $report = $this->financialReportService->generateIncomeStatement($year, $clientId);
            
            $consolidatedRevenues += $report['total_revenues'];
            $consolidatedExpenses += $report['total_expenses'];
            $consolidatedNetIncome += $report['net_income'];

            // On agrège les détails par numéro de compte
            foreach ($report['details']['revenues'] as $revenue) {
                $accountName = $revenue['account'];
                if (!isset($combinedDetails['revenues'][$accountName])) {
                    $combinedDetails['revenues'][$accountName] = 0;
                }
                $combinedDetails['revenues'][$accountName] += $revenue['amount'];
            }

            foreach ($report['details']['expenses'] as $expense) {
                $accountName = $expense['account'];
                if (!isset($combinedDetails['expenses'][$accountName])) {
                    $combinedDetails['expenses'][$accountName] = 0;
                }
                $combinedDetails['expenses'][$accountName] += $expense['amount'];
            }
        }

        // Transformer les tableaux associatifs en tableaux indexés pour le JSON
        $formattedRevenues = [];
        foreach ($combinedDetails['revenues'] as $acc => $amt) {
            $formattedRevenues[] = ['account' => $acc, 'amount' => $amt];
        }

        $formattedExpenses = [];
        foreach ($combinedDetails['expenses'] as $acc => $amt) {
            $formattedExpenses[] = ['account' => $acc, 'amount' => $amt];
        }

        return [
            'year' => $year,
            'consolidated_entities_count' => count($clientIds),
            'total_revenues' => round($consolidatedRevenues, 2),
            'total_expenses' => round($consolidatedExpenses, 2),
            'net_income' => round($consolidatedNetIncome, 2),
            'details' => [
                'revenues' => $formattedRevenues,
                'expenses' => $formattedExpenses
            ]
        ];
    }

    /**
     * Consolide le Bilan pour un groupe d'entreprises.
     */
    public function consolidateBalanceSheet(int $year, array $clientIds)
    {
        $consolidatedAssets = 0;
        $consolidatedLiabilities = 0;
        $combinedDetails = [
            'assets' => [],
            'liabilities' => []
        ];

        foreach ($clientIds as $clientId) {
            $report = $this->financialReportService->generateBalanceSheet($year, $clientId);
            
            $consolidatedAssets += $report['total_assets'];
            $consolidatedLiabilities += $report['total_liabilities_and_equity'];

            foreach ($report['details']['assets'] as $asset) {
                $accountName = $asset['account'];
                if (!isset($combinedDetails['assets'][$accountName])) {
                    $combinedDetails['assets'][$accountName] = 0;
                }
                $combinedDetails['assets'][$accountName] += $asset['amount'];
            }

            foreach ($report['details']['liabilities'] as $liability) {
                $accountName = $liability['account'];
                if (!isset($combinedDetails['liabilities'][$accountName])) {
                    $combinedDetails['liabilities'][$accountName] = 0;
                }
                $combinedDetails['liabilities'][$accountName] += $liability['amount'];
            }
        }

        $formattedAssets = [];
        foreach ($combinedDetails['assets'] as $acc => $amt) {
            $formattedAssets[] = ['account' => $acc, 'amount' => $amt];
        }

        $formattedLiabilities = [];
        foreach ($combinedDetails['liabilities'] as $acc => $amt) {
            $formattedLiabilities[] = ['account' => $acc, 'amount' => $amt];
        }

        return [
            'year' => $year,
            'consolidated_entities_count' => count($clientIds),
            'total_assets' => round($consolidatedAssets, 2),
            'total_liabilities_and_equity' => round($consolidatedLiabilities, 2),
            'is_balanced' => round($consolidatedAssets, 2) === round($consolidatedLiabilities, 2),
            'details' => [
                'assets' => $formattedAssets,
                'liabilities' => $formattedLiabilities
            ]
        ];
    }
}
