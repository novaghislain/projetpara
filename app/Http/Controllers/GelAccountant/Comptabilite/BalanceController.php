<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\EcritureComptable;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);

        $queryComptes = CompteComptable::where('cabinet_id', $cabinetId)
            ->where('actif', true)
            ->where('niveau', '>', 0);

        if ($classe = $request->input('classe')) {
            $queryComptes->where('classe', $classe);
        }

        $comptes = $queryComptes->orderBy('code')->get();
        $clientId = $request->input('client_id');
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));

        $balanceData = [];
        $totalDebit = 0;
        $totalCredit = 0;
        $totalSoldeDebit = 0;
        $totalSoldeCredit = 0;

        foreach ($comptes as $compte) {
            $lignesQuery = LigneEcriture::where('compte_id', $compte->id)
                ->whereHas('ecriture', function ($q) use ($cabinetId, $clientId, $dateFin) {
                    $q->where('cabinet_id', $cabinetId)
                      ->where('valide', true)
                      ->where('date_ecriture', '<=', $dateFin);
                    if ($clientId) {
                        $q->where('client_id', $clientId);
                    }
                });

            $totalDebitCompte = (clone $lignesQuery)->where('sens', 'debit')->sum('montant');
            $totalCreditCompte = (clone $lignesQuery)->where('sens', 'credit')->sum('montant');

            if ($totalDebitCompte === 0 && $totalCreditCompte === 0) {
                continue;
            }

            $soldeDebit = 0;
            $soldeCredit = 0;
            if ($totalDebitCompte >= $totalCreditCompte) {
                $soldeDebit = $totalDebitCompte - $totalCreditCompte;
            } else {
                $soldeCredit = $totalCreditCompte - $totalDebitCompte;
            }

            $totalDebit += $totalDebitCompte;
            $totalCredit += $totalCreditCompte;
            $totalSoldeDebit += $soldeDebit;
            $totalSoldeCredit += $soldeCredit;

            $balanceData[] = [
                'code' => $compte->code,
                'intitule' => $compte->intitule,
                'total_debit' => $totalDebitCompte,
                'total_credit' => $totalCreditCompte,
                'solde_debit' => $soldeDebit,
                'solde_credit' => $soldeCredit,
            ];
        }

        return view('gel-accountant.comptabilite.balance.index', compact(
            'balanceData', 'totalDebit', 'totalCredit', 'totalSoldeDebit', 'totalSoldeCredit',
            'comptes', 'clients'
        ));
    }
}
