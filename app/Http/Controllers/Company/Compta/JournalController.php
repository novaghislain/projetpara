<?php

namespace App\Http\Controllers\Company\Compta;

use App\Http\Controllers\Controller;
use App\Models\Compta\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        return view('company', ['page' => 'compta-journaux']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10',
            'intitule' => 'required|string|max:255',
            'type' => 'required|in:ventes,achats,banque,caisse,paie,od,inventaire,financier',
            'compte_defaut_id' => 'nullable|exists:comptes,id',
        ]);

        $clientId = Auth::user()->active_client_id;
        Journal::create([...$request->only(['code', 'intitule', 'type', 'compte_defaut_id']), 'client_id' => $clientId]);

        return back()->with('success', 'Journal créé avec succès.');
    }

    public function update(Request $request, $id)
    {
        $clientId = Auth::user()->active_client_id;
        $journal = Journal::where('client_id', $clientId)->findOrFail($id);

        $request->validate([
            'intitule' => 'required|string|max:255',
            'type' => 'required|in:ventes,achats,banque,caisse,paie,od,inventaire,financier',
        ]);

        $journal->update($request->only(['intitule', 'type', 'compte_defaut_id', 'is_actif']));

        return back()->with('success', 'Journal mis à jour.');
    }

    public function destroy($id)
    {
        $clientId = Auth::user()->active_client_id;
        $journal = Journal::where('client_id', $clientId)->findOrFail($id);

        if ($journal->ecritures()->count() > 0) {
            return back()->withErrors(['error' => 'Impossible de supprimer un journal avec des écritures.']);
        }

        $journal->delete();
        return back()->with('success', 'Journal supprimé.');
    }
}
