<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrandLivreController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = LigneEcriture::whereHas('ecriture', function ($q) use ($cabinetId) {
            $q->where('cabinet_id', $cabinetId)->where('valide', true);
        })->with(['ecriture.journal', 'compte', 'ecriture.client']);

        if ($clientId = $request->input('client_id')) {
            $query->whereHas('ecriture', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

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

        $lignes = $query->orderBy('ecriture_id')->orderBy('id')->get();
        $comptes = CompteComptable::where('cabinet_id', $cabinetId)
            ->where('actif', true)
            ->where('niveau', '>', 0)
            ->orderBy('code')
            ->get(['id', 'code', 'intitule']);
        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);

        return view('gel-accountant.comptabilite.grand-livre.index', compact('lignes', 'comptes', 'clients'));
    }
}
