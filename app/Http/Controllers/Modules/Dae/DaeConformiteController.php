<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeConformite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DaeConformiteController extends Controller
{
    public function index(Request $request)
    {
        $query = DaeConformite::with(['client', 'verifiedBy'])->orderBy('created_at', 'desc');

        if ($request->filled('type')) $query->where('type_conformite', $request->type);
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('recherche')) {
            $s = $request->recherche;
            $query->where(function ($q) use ($s) {
                $q->where('titre', 'like', "%{$s}%")
                  ->orWhere('exigence_reglementaire', 'like', "%{$s}%");
            });
        }

        $items = $query->paginate(20);
        if ($request->expectsJson()) return response()->json($items);
        return view('app', ['page' => 'dae-conformite']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'             => 'required|exists:clients,id',
            'type_conformite'       => 'required|string|max:200',
            'titre'                 => 'required|string|max:500',
            'description'           => 'nullable|string',
            'exigence_reglementaire' => 'nullable|string',
            'autorite_competente'    => 'nullable|string|max:255',
            'date_soumission'       => 'nullable|date',
            'date_expiration'       => 'nullable|date',
            'date_validation'       => 'nullable|date',
            'statut'                => 'nullable|in:a_faire,en_cours,valide,non_conforme,expire',
            'pieces_jointes'        => 'nullable|json',
            'notes'                 => 'nullable|string',
        ]);

        $validated['statut'] ??= 'a_faire';

        $item = DaeConformite::create($validated);

        if ($request->expectsJson()) return response()->json($item, 201);
        return redirect()->route('dae.conformite.index')->with('success', 'Élément de conformité créé.');
    }

    public function show($id)
    {
        $item = DaeConformite::with(['client', 'verifiedBy'])->findOrFail($id);
        if (request()->expectsJson()) return response()->json($item);
        return view('app', ['page' => 'dae-conformite-show']);
    }

    public function update(Request $request, $id)
    {
        $item = DaeConformite::findOrFail($id);

        $validated = $request->validate([
            'type_conformite'       => 'sometimes|string|max:200',
            'titre'                 => 'sometimes|string|max:500',
            'description'           => 'nullable|string',
            'exigence_reglementaire' => 'nullable|string',
            'autorite_competente'    => 'nullable|string|max:255',
            'date_soumission'       => 'nullable|date',
            'date_expiration'       => 'nullable|date',
            'pieces_jointes'        => 'nullable|json',
            'notes'                 => 'nullable|string',
        ]);

        $item->update($validated);

        if ($request->expectsJson()) return response()->json($item);
        return redirect()->route('dae.conformite.index')->with('success', 'Conformité mise à jour.');
    }

    public function destroy($id)
    {
        $item = DaeConformite::findOrFail($id);
        $item->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Élément supprimé.']);
        return redirect()->route('dae.conformite.index')->with('success', 'Élément supprimé.');
    }

    public function verifierStatut(Request $request, $id)
    {
        $item = DaeConformite::findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|in:a_faire,en_cours,valide,non_conforme,expire',
            'notes'  => 'nullable|string',
        ]);

        $updateData = ['statut' => $validated['statut']];

        if ($validated['statut'] === 'valide') {
            $updateData['date_validation'] = now();
            $updateData['verified_by'] = Auth::id();
        }

        if (!empty($validated['notes'])) {
            $pieces = $item->pieces_jointes ?? [];
            $pieces[] = ['type' => 'note', 'contenu' => $validated['notes'], 'date' => now()->toDateString()];
            $updateData['pieces_jointes'] = $pieces;
        }

        $item->update($updateData);

        if ($request->expectsJson()) return response()->json($item);
        return redirect()->back()->with('success', 'Statut mis à jour.');
    }
}
