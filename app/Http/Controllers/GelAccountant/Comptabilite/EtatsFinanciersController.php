<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtatsFinanciersController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);

        $clientId = $request->input('client_id');
        $type = $request->input('type', 'bilan');
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));

        $actif = [];
        $passif = [];
        $produits = [];
        $charges = [];
        $totalProduits = 0;
        $totalCharges = 0;

        if ($clientId) {
            $comptes = CompteComptable::where('cabinet_id', $cabinetId)
                ->where('actif', true)
                ->where('niveau', '>', 0)
                ->orderBy('code')
                ->get();

            foreach ($comptes as $compte) {
                $lignesQuery = LigneEcriture::where('compte_id', $compte->id)
                    ->whereHas('ecriture', function ($q) use ($cabinetId, $clientId, $dateFin) {
                        $q->where('cabinet_id', $cabinetId)
                          ->where('client_id', $clientId)
                          ->where('valide', true)
                          ->where('date_ecriture', '<=', $dateFin);
                    });

                $totalDebit = (clone $lignesQuery)->where('sens', 'debit')->sum('montant');
                $totalCredit = (clone $lignesQuery)->where('sens', 'credit')->sum('montant');
                $solde = $totalDebit - $totalCredit;

                if ($totalDebit === 0 && $totalCredit === 0) {
                    continue;
                }

                if (in_array($compte->classe, ['2', '3', '5']) || ($compte->classe === '4' && in_array($compte->type, ['actif']))) {
                    // Actif du bilan (classes 2, 3, 5, et certains 4)
                    $actif[] = [
                        'code' => $compte->code,
                        'intitule' => $compte->intitule,
                        'brut' => $totalDebit,
                        'amortissement' => 0,
                        'net' => $totalDebit,
                    ];
                } elseif (in_array($compte->classe, ['1']) || ($compte->classe === '4' && $compte->type === 'passif')) {
                    // Passif (classes 1, et certains 4)
                    $passif[] = [
                        'code' => $compte->code,
                        'intitule' => $compte->intitule,
                        'montant' => $totalCredit,
                    ];
                } elseif ($compte->classe === '7') {
                    // Produits
                    $montant = $totalCredit - $totalDebit;
                    if ($montant > 0) {
                        $produits[] = [
                            'code' => $compte->code,
                            'intitule' => $compte->intitule,
                            'montant' => $montant,
                        ];
                        $totalProduits += $montant;
                    }
                } elseif ($compte->classe === '6') {
                    // Charges
                    $montant = $totalDebit - $totalCredit;
                    if ($montant > 0) {
                        $charges[] = [
                            'code' => $compte->code,
                            'intitule' => $compte->intitule,
                            'montant' => $montant,
                        ];
                        $totalCharges += $montant;
                    }
                }
            }
        }

        return view('gel-accountant.comptabilite.etats-financiers.index', compact(
            'clients', 'type', 'actif', 'passif', 'produits', 'charges', 'totalProduits', 'totalCharges'
        ));
    }
}
