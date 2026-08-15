<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\FixedAsset;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur Immobilisations (Secrétariat GEL) — Registre des actifs + plan d'amortissement.
 */
class ImmobilisationController extends Controller
{
    /** Affiche le registre des immobilisations du client actif. */
    public function index(Request $request)
    {
        $clientId = session('gel_client_id') ?? $request->client_id;
        $client   = Client::where('id', $clientId)->firstOrFail();

        $assets = FixedAsset::where('client_id', $clientId)
            ->where('cabinet_id', Auth::user()->cabinet_id)
            ->orderBy('date_acquisition', 'desc')
            ->get()
            ->map(function ($asset) {
                // Calcul du plan d'amortissement annuel simplifié (linéaire)
                $asset->annuite = $asset->duree_vie > 0
                    ? round(($asset->cout_acquisition - $asset->valeur_residuelle) / $asset->duree_vie, 0)
                    : 0;
                $asset->progression = $asset->cout_acquisition > 0
                    ? min(100, round(($asset->amort_cumule / $asset->cout_acquisition) * 100))
                    : 0;
                return $asset;
            });

        return view('gel-secretary.immobilisations.index', compact('client', 'assets'));
    }

    /** Enregistre une nouvelle immobilisation. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'        => 'required|exists:gel_clients,id',
            'nom'              => 'required|string|max:255',
            'description'      => 'nullable|string',
            'date_acquisition' => 'required|date',
            'cout_acquisition' => 'required|numeric|min:0',
            'valeur_residuelle'=> 'nullable|numeric|min:0',
            'duree_vie'        => 'required|integer|min:1',
            'methode_amort'    => 'required|in:lineaire,degressif',
        ]);

        $cout  = (float) $validated['cout_acquisition'];
        $resid = (float) ($validated['valeur_residuelle'] ?? 0);

        FixedAsset::create(array_merge($validated, [
            'cabinet_id'       => Auth::user()->cabinet_id,
            'amort_cumule'     => 0,
            'vnc'              => $cout,
            'statut'           => 'actif',
            'valeur_residuelle'=> $resid,
        ]));

        return back()->with('success', 'Immobilisation enregistrée avec succès.');
    }

    /** Archive (soft delete) une immobilisation. */
    public function destroy($id)
    {
        FixedAsset::findOrFail($id)->delete();
        return back()->with('success', 'Immobilisation archivée.');
    }
}
