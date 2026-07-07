<?php

namespace App\Http\Controllers\Company\Compta;

use App\Http\Controllers\Controller;
use App\Models\Compta\Rapprochement;
use App\Models\Compta\Compte;
use App\Services\Accounting\BankMatchingEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanqueController extends Controller
{
    public function __construct(private BankMatchingEngineService $engine) {}

    public function index(Request $request)
    {
        return view('company', ['page' => 'compta-banque']);
    }

    public function link(Request $request)
    {
        // Connexion banque via API (Plaid/Nordigen) — à implémenter
        return response()->json(['message' => 'Fonctionnalité à venir : connexion bancaire API.'], 501);
    }

    public function importStatement(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
            'compte_id' => 'required|exists:comptes,id',
        ]);
        // Import du relevé CSV/OFX — logique à étendre
        return back()->with('success', 'Relevé importé avec succès.');
    }

    public function rapprochements(Request $request)
    {
        return view('company', ['page' => 'compta-banque']);
    }

    public function storeRapprochement(Request $request)
    {
        $request->validate([
            'compte_id' => 'required|exists:comptes,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'solde_depart' => 'required|numeric',
            'solde_fin' => 'required|numeric',
        ]);

        $clientId = Auth::user()->active_client_id;
        Rapprochement::create([
            ...$request->only(['compte_id', 'date_debut', 'date_fin', 'solde_depart', 'solde_fin']),
            'client_id' => $clientId,
        ]);

        return back()->with('success', 'Rapprochement créé.');
    }

    public function autoMatch(Request $request, $id)
    {
        $clientId = Auth::user()->active_client_id;
        $rapprochement = Rapprochement::where('client_id', $clientId)->findOrFail($id);

        $bankStatements = $request->input('statements', []);
        $journalEntries = $request->input('entries', []);

        $results = $this->engine->autoMatch($bankStatements, $journalEntries);

        return response()->json([
            'matches' => $results,
            'auto_matched' => collect($results)->where('action', 'MATCH_AUTOMATIQUE')->count(),
            'suggestions' => collect($results)->where('action', 'SUGGESTION')->count(),
            'manuels' => collect($results)->where('action', 'MANUEL')->count(),
        ]);
    }

    public function verify(Request $request, $id)
    {
        $clientId = Auth::user()->active_client_id;
        $rapprochement = Rapprochement::where('client_id', $clientId)->findOrFail($id);

        $rapprochement->update([
            'is_valide' => true,
            'validated_by' => Auth::id(),
            'validated_at' => now(),
            'lignes_rapprochees' => $request->input('lignes_rapprochees', []),
        ]);

        return back()->with('success', 'Rapprochement validé.');
    }
}
