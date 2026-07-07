<?php

namespace App\Http\Controllers\GelBusiness\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\CompteComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrandLivreController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->active_client_id;

        if (!$clientId) {
            return view('gel-business.comptabilite.grand-livre.index', [
                'lignes' => collect([]),
                'comptes' => collect([]),
            ]);
        }

        $client = \App\Models\Gel\Client::find($clientId);
        $cabinetId = $client->cabinet_id;

        $query = LigneEcriture::whereHas('ecriture', function ($q) use ($cabinetId, $clientId) {
            $q->where('cabinet_id', $cabinetId)
              ->where('client_id', $clientId)
              ->where('valide', true);
        })->with(['ecriture.journal', 'compte']);

        if ($compteId = $request->input('compte_id')) {
            $query->where('compte_id', $compteId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereHas('ecriture', function ($q) use ($dateFrom) {
                $q->where('date_ecriture', '>=', $dateFrom);
            });
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereHas('ecriture', function ($q) use ($dateTo) {
                $q->where('date_ecriture', '<=', $dateTo);
            });
        }

        $lignes = $query->orderBy('ecriture_id')->get();

        $comptes = CompteComptable::where('cabinet_id', $cabinetId)
            ->where('actif', true)
            ->where('niveau', '>', 0)
            ->orderBy('code')
            ->get(['id', 'code', 'intitule']);

        return view('gel-business.comptabilite.grand-livre.index', compact('lignes', 'comptes'));
    }
}
