<?php

namespace App\Http\Controllers\Company\Compta\Modules;

use App\Http\Controllers\Controller;
use App\Models\AccountingScolaireEleve;
use App\Models\AccountingScolaireFacture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScolaireController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    public function eleves()
    {
        $clientId = $this->getClientId();
        $eleves = AccountingScolaireEleve::forClient($clientId)->get();
        return response()->json($eleves);
    }

    public function storeEleve(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'matricule' => 'nullable|string|max:50',
            'date_naissance' => 'nullable|date',
        ]);
        $validated['client_id'] = $clientId;
        $eleve = AccountingScolaireEleve::create($validated);
        return response()->json(['message' => 'Élève créé.', 'eleve' => $eleve], 201);
    }

    public function classes()
    {
        return response()->json([]);
    }

    public function storeClasse(Request $request)
    {
        return response()->json(['message' => 'Fonctionnalité à venir.'], 501);
    }

    public function factures()
    {
        $clientId = $this->getClientId();
        $factures = AccountingScolaireFacture::forClient($clientId)->get();
        return response()->json($factures);
    }
}
