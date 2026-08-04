<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de la balance des comptes.
 *
 * Génère la balance générale à partir des écritures validées d'un
 * cabinet. Pour chaque compte actif de niveau > 0, la balance affiche
 * le total débit, le total crédit et le solde (débiteur ou créditeur).
 * La balance peut être filtrée par client, par classe comptable et
 * par date d'arrêté.
 */
class BalanceController extends Controller
{
    /**
     * Affiche la balance des comptes.
     *
     * Parcourt tous les comptes actifs (niveau > 0) du cabinet,
     * cumule les montants débit et crédit des écritures validées
     * jusqu'à la date d'arrêté, et calcule le solde de chaque compte.
     * Les comptes sans mouvement sont exclus. Les totaux généraux
     * (débit, crédit, soldes débiteurs/créditeurs) sont transmis à
     * la vue.
     *
     * @param  Request $request La requête avec les filtres optionnels
     *                          (client_id, classe, date_fin).
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);

        $queryComptes = CompteComptable::where('cabinet_id', $cabinetId)
            ->where('actif', true)
            ->where('niveau', '>', 0);

        // Filtre optionnel par classe comptable
        if ($classe = $request->input('classe')) {
            $queryComptes->where('classe', $classe);
        }

        $comptes = $queryComptes->orderBy('code')->get();
        $clientId = $request->input('client_id');
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));

        // Totaux cumulés de la balance
        $balanceData = [];
        $totalDebit = 0;
        $totalCredit = 0;
        $totalSoldeDebit = 0;
        $totalSoldeCredit = 0;

        // Calcul des totaux et soldes pour chaque compte
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

            // On ignore les comptes sans aucune écriture
            if ($totalDebitCompte === 0 && $totalCreditCompte === 0) {
                continue;
            }

            // Détermination du solde : si débit >= crédit, solde débiteur, sinon créditeur
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
        ) + ['currentSection' => 'comptabilite', 'currentPage' => 'balance']);
    }
}
