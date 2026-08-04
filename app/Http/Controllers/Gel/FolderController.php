<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ClientFolder;
use App\Models\Client;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Contrôleur de gestion des dossiers documentaires clients.
     * Permet de créer, modifier, supprimer et lister les dossiers
     * organisés en arborescence pour chaque client.
     */

    /**
     * Page explorateur de dossiers pour un client.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\View\View
     */
    public function index($clientId)
    {
        return view('app', [
            'page' => 'gel-dossiers',
            'clientId' => $clientId,
        ]);
    }

    /**
     * Crée un nouveau dossier.
     *
     * @param Request $request La requête HTTP contenant les données du dossier
     * @return \Illuminate\Http\JsonResponse Le dossier créé
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:client_folders,id',
            'sort_order' => 'nullable|integer',
            'is_system' => 'boolean',
        ]);

        $folder = ClientFolder::create($validated);

        return response()->json($folder, 201);
    }

    /**
     * Met à jour un dossier existant.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant du dossier
     * @return \Illuminate\Http\JsonResponse Le dossier mis à jour
     */
    public function update(Request $request, $id)
    {
        $folder = ClientFolder::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:client_folders,id',
            'sort_order' => 'nullable|integer',
        ]);

        $folder->update($validated);

        return response()->json($folder);
    }

    /**
     * Supprime un dossier.
     *
     * @param int $id L'identifiant du dossier
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function destroy($id)
    {
        $folder = ClientFolder::findOrFail($id);

        // Empêche la suppression des dossiers système
        if ($folder->is_system) {
            return response()->json(['message' => 'Impossible de supprimer un dossier système'], 403);
        }

        $folder->delete();

        return response()->json(['message' => 'Dossier supprimé']);
    }

    // ─── API ────────────────────────────────────────────────────

    /**
     * API : Liste des dossiers d'un client avec leur arborescence.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse L'arborescence des dossiers
     */
    public function listAll($clientId)
    {
        // Récupération des dossiers racine avec leurs enfants et le comptage de documents
        $folders = ClientFolder::where('client_id', $clientId)
            ->with(['children' => fn($q) => $q->withCount('documents')])
            ->withCount('documents')
            ->root()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json($folders);
    }
}
