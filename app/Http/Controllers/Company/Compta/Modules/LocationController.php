<?php

namespace App\Http\Controllers\Company\Compta\Modules;

use App\Http\Controllers\Controller;
use App\Models\AccountingLocationBien;
use App\Models\AccountingQuittance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    public function biens()
    {
        $clientId = $this->getClientId();
        $biens = AccountingLocationBien::forClient($clientId)->get();
        return response()->json($biens);
    }

    public function storeBien(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string',
            'loyer_mensuel' => 'required|numeric|min:0',
            'statut' => 'nullable|string|max:20',
        ]);
        $validated['client_id'] = $clientId;
        $bien = AccountingLocationBien::create($validated);
        return response()->json(['message' => 'Bien créé.', 'bien' => $bien], 201);
    }

    public function locataires()
    {
        return response()->json([]);
    }

    public function quittances()
    {
        $clientId = $this->getClientId();
        $quittances = AccountingQuittance::forClient($clientId)->get();
        return response()->json($quittances);
    }

    public function storeQuittance(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'location_bien_id' => 'required|exists:accounting_location_biens,id',
            'locataire_nom' => 'required|string|max:255',
            'mois' => 'required|string|max:7',
            'montant' => 'required|numeric|min:0',
            'date_paiement' => 'nullable|date',
        ]);
        $validated['client_id'] = $clientId;
        $quittance = AccountingQuittance::create($validated);
        return response()->json(['message' => 'Quittance créée.', 'quittance' => $quittance], 201);
    }
}
