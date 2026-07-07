<?php

namespace App\Http\Controllers\GelBusiness\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\LigneEcriture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtatsFinanciersController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->active_client_id;

        $type = $request->input('type', 'bilan');
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));
        $actif = [];
        $passif = [];
        $produits = [];
        $charges = [];
        $totalProduits = 0;
        $totalCharges = 0;

        if ($clientId) {
            $client = \App\Models\Gel\Client::find($clientId);
            $cabinetId = $client->cabinet_id;

            $comptes = CompteComptable::where('cabinet_id', $cabinetId)
                ->where('actif', true)->where('niveau', '>', 0)->get();

            foreach ($comptes as $compte) {
                $lignesQuery = LigneEcriture::where('compte_id', $compte->id)
                    ->whereHas('ecriture', function ($q) use ($cabinetId, $clientId, $dateFin) {
                        $q->where('cabinet_id', $cabinetId)
                          ->where('client_id', $clientId)
                          ->where('valide', true)
                          ->where('date_ecriture', '<=', $dateFin);
                    });

                $td = (clone $lignesQuery)->where('sens', 'debit')->sum('montant');
                $tc = (clone $lignesQuery)->where('sens', 'credit')->sum('montant');

                if ($td === 0 && $tc === 0) continue;

                if (in_array($compte->classe, ['2', '3', '5']) || ($compte->classe === '4' && $compte->type === 'actif')) {
                    $actif[] = ['code' => $compte->code, 'intitule' => $compte->intitule, 'brut' => $td, 'amortissement' => 0, 'net' => $td];
                } elseif (in_array($compte->classe, ['1']) || ($compte->classe === '4' && $compte->type === 'passif')) {
                    $passif[] = ['code' => $compte->code, 'intitule' => $compte->intitule, 'montant' => $tc];
                } elseif ($compte->classe === '7') {
                    $m = $tc - $td;
                    if ($m > 0) { $produits[] = ['code' => $compte->code, 'intitule' => $compte->intitule, 'montant' => $m]; $totalProduits += $m; }
                } elseif ($compte->classe === '6') {
                    $m = $td - $tc;
                    if ($m > 0) { $charges[] = ['code' => $compte->code, 'intitule' => $compte->intitule, 'montant' => $m]; $totalCharges += $m; }
                }
            }
        }

        return view('gel-business.comptabilite.etats-financiers.index', compact(
            'type', 'actif', 'passif', 'produits', 'charges', 'totalProduits', 'totalCharges'
        ));
    }
}
