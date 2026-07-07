<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImmobilisationsController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = FixedAsset::where('cabinet_id', $cabinetId);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $assets = $query->with('client:id,nom_entreprise')
            ->orderBy('date_acquisition', 'desc')
            ->paginate(20);

        return view('gel-accountant.comptabilite.immobilisations.index', compact('assets'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'client_id' => 'nullable|exists:gel_clients,id',
            'compte_id' => 'nullable|exists:gel_plan_comptable,id',
            'date_acquisition' => 'required|date',
            'cout_acquisition' => 'required|numeric|min:0',
            'valeur_residuelle' => 'nullable|numeric|min:0',
            'duree_vie' => 'required|integer|min:1',
            'methode_amort' => 'required|in:lineaire,degressif',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;
        $validated['statut'] = 'actif';

        // Calcul du taux d'amortissement
        if ($validated['methode_amort'] === 'lineaire') {
            $validated['taux_amort'] = round(100 / $validated['duree_vie'], 2);
        } else {
            $validated['taux_amort'] = round((100 / $validated['duree_vie']) * 1.75, 2);
        }

        // VNC initiale = coût d'acquisition
        $validated['vnc'] = $validated['cout_acquisition'];
        $validated['amort_cumule'] = 0;

        FixedAsset::create($validated);

        return redirect()->route('gel-accountant.comptabilite.immobilisations')
            ->with('success', 'Actif créé avec succès.');
    }

    public function update(Request $request, $id)
    {
        $asset = FixedAsset::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'valeur_residuelle' => 'nullable|numeric|min:0',
            'statut' => 'required|in:actif,cede,mis_au_rebut',
        ]);

        $asset->update($validated);

        return redirect()->route('gel-accountant.comptabilite.immobilisations')
            ->with('success', 'Actif mis à jour.');
    }

    public function destroy($id)
    {
        $asset = FixedAsset::findOrFail($id);
        $asset->delete();

        return redirect()->route('gel-accountant.comptabilite.immobilisations')
            ->with('success', 'Actif supprimé.');
    }
}
