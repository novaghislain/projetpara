<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\SavedReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des rapports sauvegardés.
 *
 * Permet de lister, créer et supprimer des rapports personnalisés
 * dans un cabinet comptable. Les rapports peuvent être partagés
 * entre tous les utilisateurs du cabinet.
 */
class RapportsController extends Controller
{
    /**
     * Affiche la liste des rapports sauvegardés.
     *
     * Les rapports propres au cabinet et ceux partagés (sans cabinet
     * spécifique) sont affichés ensemble.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        // Récupère les rapports du cabinet ainsi que les rapports
        // partagés qui ne sont pas rattachés à un cabinet spécifique
        $savedReports = SavedReport::where('cabinet_id', $cabinetId)
            ->orWhere(function ($q) use ($cabinetId) {
                $q->whereNull('cabinet_id')->where('partage', true);
            })
            ->orderBy('nom')
            ->get();

        $stats = [
            'sauvegardes' => $savedReports->count(),
        ];

        return view('gel-accountant.rapports.index', compact('savedReports', 'stats') + ['currentSection' => 'rapports', 'currentPage' => 'rapports']);
    }

    /**
     * Affiche le formulaire de création d'un rapport.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('gel-accountant.rapports.create', ['currentSection' => 'rapports', 'currentPage' => 'rapports']);
    }

    /**
     * Sauvegarde un nouveau rapport personnalisé.
     *
     * @param  Request $request La requête contenant la configuration
     *                          du rapport (nom, type, filtres, colonnes).
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'client_id' => 'nullable|exists:gel_clients,id',
            'filtres' => 'nullable|json',
            'colonnes' => 'nullable|json',
            'configuration' => 'nullable|json',
            'partage' => 'boolean',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;

        SavedReport::create($validated);

        return redirect()->route('gel-accountant.rapports')
            ->with('success', 'Rapport sauvegardé.');
    }

    /**
     * Supprime un rapport sauvegardé.
     *
     * @param  int $id L'identifiant du rapport à supprimer.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $report = SavedReport::findOrFail($id);
        $report->delete();

        return redirect()->route('gel-accountant.rapports')
            ->with('success', 'Rapport supprimé.');
    }
}
