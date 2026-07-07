<?php

namespace App\Http\Controllers\Company\Compta;

use App\Http\Controllers\Controller;
use App\Models\Compta\Compte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompteController extends Controller
{
    public function index(Request $request)
    {
        $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;

        if (!$clientId) {
            return redirect()->route('select.context')
                ->withErrors(['Aucune entreprise associée.']);
        }

        return view('company', [
            'page' => 'compta-comptes',
            'clientId' => $clientId,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:20',
            'intitule' => 'required|string|max:255',
            'classe' => 'nullable|integer|min:1|max:9',
            'type' => 'required|in:actif,passif,charge,produit,autre',
            'parent_id' => 'nullable|exists:comptes,id',
        ]);

        $clientId = Auth::user()->active_client_id;

        $compte = Compte::create([
            ...$request->only(['numero', 'intitule', 'classe', 'type', 'sous_type', 'parent_id']),
            'client_id' => $clientId,
        ]);

        return back()->with('success', "Compte {$compte->numero} créé avec succès.");
    }

    public function update(Request $request, $id)
    {
        $clientId = Auth::user()->active_client_id;
        $compte = Compte::where('client_id', $clientId)->findOrFail($id);

        $request->validate([
            'intitule' => 'required|string|max:255',
            'type' => 'required|in:actif,passif,charge,produit,autre',
        ]);

        $compte->update($request->only(['intitule', 'type', 'sous_type', 'is_actif', 'is_verrouille']));

        return back()->with('success', 'Compte mis à jour.');
    }

    public function destroy($id)
    {
        $clientId = Auth::user()->active_client_id;
        $compte = Compte::where('client_id', $clientId)->findOrFail($id);

        // Vérifier que le compte n'a pas de mouvements
        if ($compte->lignes_ecritures()->count() > 0) {
            return back()->withErrors(['error' => 'Impossible de supprimer un compte mouvementé.']);
        }

        $compte->delete();
        return back()->with('success', 'Compte supprimé.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);
        $clientId = Auth::user()->active_client_id;
        $count = 0;

        $handle = fopen($request->file->getRealPath(), 'r');
        fgetcsv($handle); // Sauter l'en-tête
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 4) {
                Compte::firstOrCreate(
                    ['client_id' => $clientId, 'numero' => trim($row[0])],
                    [
                        'intitule' => trim($row[1]),
                        'classe' => (int) $row[2],
                        'type' => trim($row[3]),
                    ]
                );
                $count++;
            }
        }
        fclose($handle);

        return back()->with('success', "{$count} comptes importés avec succès.");
    }
}
