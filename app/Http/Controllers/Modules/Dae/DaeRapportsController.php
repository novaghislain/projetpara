<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeRapport;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des rapports du module DAE.
 *
 * Permet la génération, la consultation et le téléchargement
 * des rapports avec filtrage par type et période.
 */
class DaeRapportsController extends BaseDaeController
{
    /**
     * Liste paginée des rapports avec filtres.
     *
     * Filtres disponibles : type_rapport, statut, période (periode_debut, periode_fin).
     *
     * @param Request $request La requête HTTP avec les paramètres de filtre
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = DaeRapport::with('client')->orderBy('created_at', 'desc');

        if ($request->filled('type_rapport')) $query->where('type_rapport', $request->type_rapport);
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        $query->where('client_id', $this->getClientId($request));
        if ($request->filled('periode_debut')) $query->whereDate('periode_debut', '>=', $request->periode_debut);
        if ($request->filled('periode_fin')) $query->whereDate('periode_fin', '<=', $request->periode_fin);

        $rapports = $query->paginate(20);
        if ($request->expectsJson()) return response()->json($rapports);
        return view('app', ['page' => 'dae-rapports']);
    }

    /**
     * Crée un nouveau rapport (brouillon).
     *
     * @param Request $request La requête HTTP avec les données du rapport
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function generer(Request $request)
    {
        $validated = $request->validate([
            'titre'         => 'required|string|max:500',
            'type_rapport'   => 'required|string|max:200',
            'description'   => 'nullable|string',
            'periode_debut' => 'nullable|date',
            'periode_fin'   => 'nullable|date',
        ]);

        $validated['statut'] = 'brouillon';
        $validated['client_id'] = $this->getClientId($request);

        $rapport = DaeRapport::create($validated);

        if ($request->expectsJson()) return response()->json($rapport, 201);
        return redirect()->route('dae.rapports.index')->with('success', 'Rapport créé.');
    }

    /**
     * Affiche un rapport spécifique.
     *
     * @param int $id L'identifiant du rapport
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        $rapport = DaeRapport::with('client')->findOrFail($id);
        if (request()->expectsJson()) return response()->json($rapport);
        return view('app', ['page' => 'dae-rapports-show']);
    }

    /**
     * Télécharge le fichier d'un rapport.
     *
     * @param int $id L'identifiant du rapport
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function telecharger($id)
    {
        $rapport = DaeRapport::findOrFail($id);
        if (!$rapport->fichier) abort(404);
        return \Illuminate\Support\Facades\Storage::disk('public')->download($rapport->fichier);
    }

    /**
     * Supprime un rapport.
     *
     * @param int $id L'identifiant du rapport à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $rapport = DaeRapport::findOrFail($id);
        $rapport->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Rapport supprimé.']);
        return redirect()->route('dae.rapports.index')->with('success', 'Rapport supprimé.');
    }
}
