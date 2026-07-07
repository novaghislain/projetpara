<?php

namespace App\Http\Controllers\Company\Compta\Modules;

use App\Http\Controllers\Controller;
use App\Models\AccountingMorgueDepot;
use App\Models\AccountingMorgueFacture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MorgueController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    public function depots()
    {
        $clientId = $this->getClientId();
        $depots = AccountingMorgueDepot::forClient($clientId)->get();
        return response()->json($depots);
    }

    public function storeDepot(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'defunt_nom' => 'required|string|max:255',
            'defunt_prenom' => 'nullable|string|max:255',
            'famille_contact' => 'nullable|string|max:50',
            'type_conservation' => 'required|string|max:50',
            'tarif_journalier' => 'nullable|numeric|min:0',
            'date_deces' => 'nullable|date',
            'date_depot' => 'nullable|date',
        ]);
        $validated['client_id'] = $clientId;
        $validated['numero_dossier'] = 'DEP-' . now()->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $validated['statut'] = 'actif';
        $depot = AccountingMorgueDepot::create($validated);
        return response()->json(['message' => 'Dépôt créé.', 'depot' => $depot], 201);
    }

    public function updateDepot(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $depot = AccountingMorgueDepot::forClient($clientId)->findOrFail($id);
        $depot->update($request->all());
        return response()->json(['message' => 'Dépôt mis à jour.', 'depot' => $depot]);
    }

    public function factures()
    {
        $clientId = $this->getClientId();
        $factures = AccountingMorgueFacture::forClient($clientId)->get();
        return response()->json($factures);
    }
}
