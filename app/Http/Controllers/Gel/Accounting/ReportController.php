<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Models\AccountingAccount;
use Illuminate\Http\Request;

class ReportController extends BaseGelAccountingController
{
    /**
     * Contrôleur de reporting comptable.
     * Fournit les API pour consulter la balance, le grand livre,
     * le bilan et le compte de résultat à partir des écritures
     * comptables validées.
     */

    /**
     * API : Balance des comptes avec soldes débiteurs/créditeurs.
     *
     * Calcule le total des débits et crédits pour chaque compte actif
     * et détermine le solde (débiteur ou créditeur).
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse La balance avec les totaux
     */
    public function balance($clientId)
    {
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->orderBy('code')
            ->get();

        $balance = $accounts->map(function ($account) {
            // Somme des débits et crédits des écritures validées
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
     * API : Grand Livre avec solde courant par compte.
     *
     * Pour chaque compte actif, liste les écritures validées avec
     * le solde cumulé après chaque ligne.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse Le grand livre détaillé
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

            // Calcul du solde courant après chaque ligne
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
     * API : Bilan comptable (Actif / Passif).
     *
     * Regroupe les comptes par type (actif, trésorerie, passif)
     * et calcule les soldes pour chaque poste du bilan.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse Le bilan avec totaux
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
     * API : Compte de résultat (Charges / Produits).
     *
     * Regroupe les comptes par type (charge, produit) et calcule
     * le résultat net (produits - charges).
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse Le compte de résultat
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
