<?php

namespace App\Services\Accounting;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportService
{
    /**
     * Génère le Compte de Résultat (Income Statement) OHADA.
     * Calcule la différence entre les Produits (Classe 7) et les Charges (Classe 6).
     */
    public function generateIncomeStatement(int $year, string $clientId)
    {
        $startDate = "$year-01-01";
        $endDate = "$year-12-31";

        // Récupérer la balance des comptes 6 et 7
        // On suppose que la table journal_entries a un account_id ou qu'on simplifie 
        // avec account_number. Si le modèle n'a pas account_number direct, on joint
        // Ici on va faire une requête simplifiée sur les entrées de journal
        
        // Pour être agnostique au schéma exact, on groupe par la racine du compte si possible
        // Dans une vraie app OHADA, on joint sur la table `accounts`
        
        $entries = DB::table('journal_entries')
            ->join('accounting_accounts', 'journal_entries.account_id', '=', 'accounting_accounts.id')
            ->where('journal_entries.client_id', $clientId)
            ->whereBetween('journal_entries.date', [$startDate, $endDate])
            ->select(
                'accounting_accounts.account_number',
                'accounting_accounts.name',
                DB::raw('SUM(journal_entries.debit) as total_debit'),
                DB::raw('SUM(journal_entries.credit) as total_credit')
            )
            ->groupBy('accounting_accounts.id', 'accounting_accounts.account_number', 'accounting_accounts.name')
            ->get();

        $revenues = 0;
        $expenses = 0;
        $details = [
            'revenues' => [],
            'expenses' => []
        ];

        foreach ($entries as $entry) {
            $balance = $entry->total_credit - $entry->total_debit; // Pour la classe 7, crédit = +, pour 6, débit = +
            
            if (str_starts_with($entry->account_number, '7')) {
                // Produits (Revenues)
                $revenues += $balance;
                $details['revenues'][] = [
                    'account' => $entry->account_number . ' - ' . $entry->name,
                    'amount' => $balance
                ];
            } elseif (str_starts_with($entry->account_number, '6')) {
                // Charges (Expenses)
                $expenseBalance = $entry->total_debit - $entry->total_credit;
                $expenses += $expenseBalance;
                $details['expenses'][] = [
                    'account' => $entry->account_number . ' - ' . $entry->name,
                    'amount' => $expenseBalance
                ];
            }
        }

        $netIncome = $revenues - $expenses;

        return [
            'year' => $year,
            'client_id' => $clientId,
            'total_revenues' => round($revenues, 2),
            'total_expenses' => round($expenses, 2),
            'net_income' => round($netIncome, 2),
            'details' => $details
        ];
    }

    /**
     * Génère le Bilan (Balance Sheet) OHADA.
     * Actif = Passif + Capitaux Propres.
     */
    public function generateBalanceSheet(int $year, string $clientId)
    {
        $endDate = "$year-12-31";

        // Le bilan prend les soldes cumulés depuis le début jusqu'à la fin de l'année
        $entries = DB::table('journal_entries')
            ->join('accounting_accounts', 'journal_entries.account_id', '=', 'accounting_accounts.id')
            ->where('journal_entries.client_id', $clientId)
            ->where('journal_entries.date', '<=', $endDate)
            ->select(
                'accounting_accounts.account_number',
                'accounting_accounts.name',
                DB::raw('SUM(journal_entries.debit) as total_debit'),
                DB::raw('SUM(journal_entries.credit) as total_credit')
            )
            ->groupBy('accounting_accounts.id', 'accounting_accounts.account_number', 'accounting_accounts.name')
            ->get();

        $assets = 0; // Actif (Classes 2, 3, 4 débiteur, 5 débiteur)
        $liabilities = 0; // Passif (Classes 1, 4 créditeur, 5 créditeur)
        
        $details = [
            'assets' => [],
            'liabilities' => []
        ];

        foreach ($entries as $entry) {
            $balance = $entry->total_debit - $entry->total_credit;
            
            $firstDigit = substr($entry->account_number, 0, 1);
            
            if (in_array($firstDigit, ['2', '3']) || ($firstDigit == '4' && $balance > 0) || ($firstDigit == '5' && $balance > 0)) {
                // Actif
                $assets += $balance;
                $details['assets'][] = [
                    'account' => $entry->account_number . ' - ' . $entry->name,
                    'amount' => $balance
                ];
            } elseif (in_array($firstDigit, ['1']) || ($firstDigit == '4' && $balance < 0) || ($firstDigit == '5' && $balance < 0)) {
                // Passif
                $creditBalance = abs($balance);
                $liabilities += $creditBalance;
                $details['liabilities'][] = [
                    'account' => $entry->account_number . ' - ' . $entry->name,
                    'amount' => $creditBalance
                ];
            }
        }

        // Ajouter le Résultat Net dans les Capitaux Propres (Passif)
        $incomeStatement = $this->generateIncomeStatement($year, $clientId);
        $netIncome = $incomeStatement['net_income'];
        
        $liabilities += $netIncome;
        $details['liabilities'][] = [
            'account' => '13 - Résultat Net de l\'Exercice',
            'amount' => $netIncome
        ];

        return [
            'year' => $year,
            'client_id' => $clientId,
            'total_assets' => round($assets, 2),
            'total_liabilities_and_equity' => round($liabilities, 2),
            'is_balanced' => round($assets, 2) === round($liabilities, 2),
            'details' => $details
        ];
    }
}
