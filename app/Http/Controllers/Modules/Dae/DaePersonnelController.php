<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaePersonnelDossier;
use Illuminate\Http\Request;

class DaePersonnelController extends BaseDaeController
{
    public function index(Request $request)
    {
        $query = DaePersonnelDossier::orderBy('nom')->orderBy('prenom');

        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('departement')) $query->where('departement', $request->departement);
        $query->where('client_id', $this->getClientId($request));
        if ($request->filled('recherche')) {
            $s = $request->recherche;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%{$s}%")
                  ->orWhere('prenom', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('poste', 'like', "%{$s}%");
            });
        }

        $personnel = $query->paginate(20);
        if ($request->expectsJson()) return response()->json($personnel);
        return view('app', ['page' => 'dae-personnel']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'          => 'required|string|max:255',
            'prenom'       => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'telephone'    => 'nullable|string|max:50',
            'poste'        => 'nullable|string|max:255',
            'departement'  => 'nullable|string|max:255',
            'date_embauche'=> 'nullable|date',
            'date_depart'  => 'nullable|date|after:date_embauche',
            'statut'       => 'nullable|in:actif,conge,suspendu,sorti',
            'type_contrat' => 'nullable|string|max:100',
            'salaire'      => 'nullable|numeric|min:0',
            'numero_securite_sociale' => 'nullable|string|max:50',
            'notes'        => 'nullable|string',
        ]);

        $validated['statut'] ??= 'actif';
        $validated['client_id'] = $this->getClientId($request);
        $personne = DaePersonnelDossier::create($validated);

        if ($request->expectsJson()) return response()->json($personne, 201);
        return redirect()->route('dae.personnel.index')->with('success', 'Membre ajouté.');
    }

    public function show($id)
    {
        $personne = DaePersonnelDossier::with('client')->findOrFail($id);
        if (request()->expectsJson()) return response()->json($personne);
        return view('app', ['page' => 'dae-personnel-show']);
    }

    public function update(Request $request, $id)
    {
        $personne = DaePersonnelDossier::findOrFail($id);

        $validated = $request->validate([
            'nom'          => 'sometimes|string|max:255',
            'prenom'       => 'sometimes|string|max:255',
            'email'        => 'nullable|email|max:255',
            'telephone'    => 'nullable|string|max:50',
            'poste'        => 'nullable|string|max:255',
            'departement'  => 'nullable|string|max:255',
            'date_embauche'=> 'nullable|date',
            'date_depart'  => 'nullable|date|after:date_embauche',
            'statut'       => 'sometimes|in:actif,conge,suspendu,sorti',
            'type_contrat' => 'nullable|string|max:100',
            'salaire'      => 'nullable|numeric|min:0',
            'numero_securite_sociale' => 'nullable|string|max:50',
            'notes'        => 'nullable|string',
        ]);

        $personne->update($validated);

        if ($request->expectsJson()) return response()->json($personne);
        return redirect()->route('dae.personnel.index')->with('success', 'Membre mis à jour.');
    }

    public function destroy($id)
    {
        $personne = DaePersonnelDossier::findOrFail($id);
        $personne->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Membre supprimé.']);
        return redirect()->route('dae.personnel.index')->with('success', 'Membre supprimé.');
    }

    public function changementsRecent(Request $request)
    {
        $query = DaePersonnelDossier::whereIn('statut', ['actif', 'conge'])->where('client_id', $this->getClientId($request));

        return response()->json([
            'effectif' => $query->count(),
            'par_departement' => DaePersonnelDossier::selectRaw('departement, count(*) as total')
                ->where('statut', 'actif')
                ->groupBy('departement')
                ->get(),
            'recents' => DaePersonnelDossier::orderBy('created_at', 'desc')->take(5)->get(),
        ]);
    }
}
