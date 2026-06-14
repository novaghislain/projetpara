<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingJournal;
use App\Models\AccountingAccount;
use App\Models\AccountingJournalLine;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * API: Balance des comptes.
     */
    public function balance($clientId)
    {
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->orderBy('code')
            ->get();

        $balance = $accounts->map(function ($account) {
            $debitTotal = $account->journalLines()
                ->whereHas('journal', fn($q) => $q->where('status', 'posted'))
                ->sum('debit');

            $creditTotal = $account->journalLines()
                ->whereHas('journal', fn($q) => $q->where('status', 'posted'))
                ->sum('credit');

            $balance = $debitTotal - $creditTotal;

            return [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'debit' => $debitTotal,
                'credit' => $creditTotal,
                'balance' => $balance,
                'balance_abs' => abs($balance),
                'sens' => $balance >= 0 ? 'Débiteur' : 'Créditeur',
            ];
        });

        return response()->json([
            'accounts' => $balance,
            'totals' => [
                'debit' => $balance->sum('debit'),
                'credit' => $balance->sum('credit'),
            ],
        ]);
    }

    /**
     * API: Grand Livre.
     */
    public function grandLivre($clientId)
    {
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->orderBy('code')
            ->get();

        $data = $accounts->map(function ($account) {
            $lines = $account->journalLines()
                ->whereHas('journal', fn($q) => $q->where('status', 'posted'))
                ->with('journal:id,entry_date,reference,description')
                ->orderBy('created_at')
                ->get();

            $runningBalance = 0;
            $linesWithBalance = $lines->map(function ($line) use (&$runningBalance) {
                $runningBalance += $line->debit - $line->credit;
                return [
                    'date' => $line->journal->entry_date->format('Y-m-d'),
                    'reference' => $line->journal->reference,
                    'label' => $line->label,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'balance' => $runningBalance,
                ];
            });

            return [
                'account' => [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                ],
                'lines' => $linesWithBalance,
                'total_debit' => $lines->sum('debit'),
                'total_credit' => $lines->sum('credit'),
                'balance' => $runningBalance,
            ];
        });

        return response()->json($data);
    }

    /**
     * API: Bilan comptable.
     */
    public function bilan($clientId)
    {
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->get();

        $actif = $accounts->filter(fn($a) => in_array($a->type, ['actif', 'tresorerie']))->values();
        $passif = $accounts->filter(fn($a) => $a->type === 'passif')->values();

        $build = function ($items) {
            return $items->map(function ($account) {
                $balance = $account->journalLines()
                    ->whereHas('journal', fn($q) => $q->where('status', 'posted'))
                    ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as balance')
                    ->value('balance');

                return [
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => abs($balance),
                ];
            });
        };

        return response()->json([
            'actif' => $build($actif),
            'passif' => $build($passif),
            'total_actif' => $build($actif)->sum('balance'),
            'total_passif' => $build($passif)->sum('balance'),
        ]);
    }

    /**
     * API: Compte de résultat.
     */
    public function resultat($clientId)
    {
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->get();

        $charges = $accounts->filter(fn($a) => $a->type === 'charge')->values();
        $produits = $accounts->filter(fn($a) => $a->type === 'produit')->values();

        $build = function ($items) {
            return $items->map(function ($account) {
                $balance = $account->journalLines()
                    ->whereHas('journal', fn($q) => $q->where('status', 'posted'))
                    ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as balance')
                    ->value('balance');

                return [
                    'code' => $account->code,
                    'name' => $account->name,
                    'montant' => abs($balance),
                ];
            });
        };

        $totalCharges = $build($charges)->sum('montant');
        $totalProduits = $build($produits)->sum('montant');

        return response()->json([
            'charges' => $build($charges),
            'produits' => $build($produits),
            'total_charges' => $totalCharges,
            'total_produits' => $totalProduits,
            'resultat' => $totalProduits - $totalCharges,
        ]);
    }
}
