<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeContrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DaeContratsController extends BaseDaeController
{
    public function index(Request $request)
    {
        $query = DaeContrat::with('client')->orderBy('created_at', 'desc');

        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('type_contrat')) $query->where('type_contrat', $request->type_contrat);
        $query->where('client_id', $this->getClientId($request));
        if ($request->filled('recherche')) {
            $s = $request->recherche;
            $query->where(function ($q) use ($s) {
                $q->where('titre', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
                  ->orWhere('partie_adverse', 'like', "%{$s}%");
            });
        }

        $contrats = $query->paginate(20);
        if ($request->expectsJson()) return response()->json($contrats);
        return view('app', ['page' => 'dae-contrats']);
    }

    public function create()
    {
        return view('app', ['page' => 'dae-contrats-create']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference'     => 'nullable|string|max:100',
            'titre'         => 'required|string|max:500',
            'type_contrat'  => 'required|string|max:200',
            'partie_adverse'=> 'nullable|string|max:255',
            'date_debut'    => 'required|date',
            'date_fin'      => 'nullable|date|after:date_debut',
            'date_preavis'  => 'nullable|date',
            'duree_mois'    => 'nullable|integer|min:1',
            'montant'       => 'nullable|numeric|min:0',
            'devise'        => 'nullable|string|max:3',
            'statut'        => 'nullable|in:brouillon,actif,expire,resilie,renouvele',
            'fichier'       => 'nullable|file|max:10240',
            'renouvelable'  => 'nullable|boolean',
            'tags'          => 'nullable|json',
        ]);

        $validated['reference'] ??= 'CT-' . strtoupper(uniqid());
        $validated['statut'] ??= 'brouillon';
        $validated['client_id'] = $this->getClientId($request);

        if ($request->hasFile('fichier')) {
            $validated['fichier'] = $request->file('fichier')->store('dae/contrats', 'public');
        }

        $contrat = DaeContrat::create($validated);

        if ($request->expectsJson()) return response()->json($contrat, 201);
        return redirect()->route('dae.contrats.index')->with('success', 'Contrat créé.');
    }

    public function show($id)
    {
        $contrat = DaeContrat::with('client')->findOrFail($id);
        if (request()->expectsJson()) return response()->json($contrat);
        return view('app', ['page' => 'dae-contrats-show']);
    }

    public function edit($id)
    {
        return view('app', ['page' => 'dae-contrats-edit']);
    }

    public function update(Request $request, $id)
    {
        $contrat = DaeContrat::findOrFail($id);

        $validated = $request->validate([
            'titre'         => 'sometimes|string|max:500',
            'type_contrat'  => 'sometimes|string|max:200',
            'partie_adverse'=> 'nullable|string|max:255',
            'date_debut'    => 'sometimes|date',
            'date_fin'      => 'nullable|date|after:date_debut',
            'date_preavis'  => 'nullable|date',
            'duree_mois'    => 'nullable|integer|min:1',
            'montant'       => 'nullable|numeric|min:0',
            'devise'        => 'nullable|string|max:3',
            'statut'        => 'sometimes|in:brouillon,actif,expire,resilie,renouvele',
            'fichier'       => 'nullable|file|max:10240',
            'renouvelable'  => 'nullable|boolean',
            'tags'          => 'nullable|json',
        ]);

        if ($request->hasFile('fichier')) {
            if ($contrat->fichier) Storage::disk('public')->delete($contrat->fichier);
            $validated['fichier'] = $request->file('fichier')->store('dae/contrats', 'public');
        }

        $contrat->update($validated);

        if ($request->expectsJson()) return response()->json($contrat);
        return redirect()->route('dae.contrats.index')->with('success', 'Contrat mis à jour.');
    }

    public function destroy($id)
    {
        $contrat = DaeContrat::findOrFail($id);
        if ($contrat->fichier) Storage::disk('public')->delete($contrat->fichier);
        $contrat->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Contrat supprimé.']);
        return redirect()->route('dae.contrats.index')->with('success', 'Contrat supprimé.');
    }

    public function renouveler(Request $request, $id)
    {
        $contrat = DaeContrat::findOrFail($id);
        $contrat->update(['statut' => 'renouvele']);

        $newValidated = $request->validate([
            'date_fin' => 'nullable|date|after:today',
            'montant'  => 'nullable|numeric|min:0',
        ]);

        $newContrat = $contrat->replicate(['reference', 'statut']);
        $newContrat->reference = 'CT-' . strtoupper(uniqid());
        $newContrat->statut = 'actif';
        $newContrat->date_debut = now();
        $newContrat->date_fin = $newValidated['date_fin'] ?? $contrat->date_fin?->addYear();
        $newContrat->montant = $newValidated['montant'] ?? $contrat->montant;
        $newContrat->save();

        if ($request->expectsJson()) return response()->json($newContrat, 201);
        return redirect()->route('dae.contrats.show', $newContrat->id)
            ->with('success', 'Contrat renouvelé.');
    }

    public function telecharger($id)
    {
        $contrat = DaeContrat::findOrFail($id);
        if (!$contrat->fichier) abort(404);
        return Storage::disk('public')->download($contrat->fichier);
    }
}
