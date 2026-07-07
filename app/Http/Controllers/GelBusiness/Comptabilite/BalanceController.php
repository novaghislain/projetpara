<?php

namespace App\Http\Controllers\GelBusiness\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\LigneEcriture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->active_client_id;

        if (!$clientId) {
            return view('gel-business.comptabilite.balance.index', [
                'balanceData' => [],
                'totalDebit' => 0,
                'totalCredit' => 0,
            ]);
        }

        $client = \App\Models\Gel\Client::find($clientId);
        $cabinetId = $client->cabinet_id;
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));

        $comptes = CompteComptable::where('cabinet_id', $cabinetId)
            ->where('actif', true)
            ->where('niveau', '>', 0)
            ->orderBy('code')
            ->get();

        $balanceData = [];
        $totalDebit = 0;
        $totalCredit = 0;

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

            $sd = $td >= $tc ? $td - $tc : 0;
            $sc = $tc > $td ? $tc - $td : 0;

            $totalDebit += $td;
            $totalCredit += $tc;

            $balanceData[] = [
                'code' => $compte->code,
                'intitule' => $compte->intitule,
                'total_debit' => $td,
                'total_credit' => $tc,
                'solde_debit' => $sd,
                'solde_credit' => $sc,
            ];
        }

        return view('gel-business.comptabilite.balance.index', compact('balanceData', 'totalDebit', 'totalCredit'));
    }
}
