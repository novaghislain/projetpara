<?php
// @deprecated — Ces contrôleurs sont obsolètes. Voir README.md dans le dossier parent.

namespace App\Http\Controllers\Company\Compta\Modules;

use App\Http\Controllers\Controller;
use App\Models\AccountingTransitDossier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransportController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    public function dossiers()
    {
        $clientId = $this->getClientId();
        $dossiers = AccountingTransitDossier::forClient($clientId)->get();
        return response()->json($dossiers);
    }

    public function storeDossier(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'numero_dossier' => 'required|string|max:50',
            'client_nom' => 'required|string|max:255',
            'type_transport' => 'required|string|max:50',
            'montant' => 'nullable|numeric|min:0',
            'statut' => 'nullable|string|max:20',
        ]);
        $validated['client_id'] = $clientId;
        $dossier = AccountingTransitDossier::create($validated);
        return response()->json(['message' => 'Dossier créé.', 'dossier' => $dossier], 201);
    }

    public function vehicules()
    {
        return response()->json([]);
    }

    public function storeVehicule(Request $request)
    {
        return response()->json(['message' => 'Fonctionnalité à venir.'], 501);
    }

    public function tournees()
    {
        return response()->json([]);
    }
}
