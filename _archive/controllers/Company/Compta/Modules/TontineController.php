<?php
// @deprecated — Ces contrôleurs sont obsolètes. Voir README.md dans le dossier parent.

namespace App\Http\Controllers\Company\Compta\Modules;

use App\Http\Controllers\Controller;
use App\Models\AccountingTontine;
use App\Models\AccountingCotisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TontineController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    public function index()
    {
        $clientId = $this->getClientId();
        $tontines = AccountingTontine::forClient($clientId)->get();
        return response()->json($tontines);
    }

    public function store(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'nombre_participants' => 'nullable|integer|min:2',
            'frequence' => 'nullable|string|max:20',
        ]);
        $validated['client_id'] = $clientId;
        $tontine = AccountingTontine::create($validated);
        return response()->json(['message' => 'Tontine créée.', 'tontine' => $tontine], 201);
    }

    public function cotisation(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'participant_nom' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date' => 'nullable|date',
        ]);
        $validated['tontine_id'] = $id;
        $cotisation = AccountingCotisation::create($validated);
        return response()->json(['message' => 'Cotisation enregistrée.', 'cotisation' => $cotisation], 201);
    }

    public function attribution(Request $request, $id)
    {
        return response()->json(['message' => 'Fonctionnalité à venir.'], 501);
    }
}
