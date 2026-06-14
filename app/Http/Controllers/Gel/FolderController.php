<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ClientFolder;
use App\Models\Client;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Page explorateur de dossiers pour un client.
     */
    public function index($clientId)
    {
        return view('app', [
            'page' => 'gel-dossiers',
            'clientId' => $clientId,
        ]);
    }

    /**
     * Créer un dossier.
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
     * Mettre à jour un dossier.
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
     * Supprimer un dossier.
     */
    public function destroy($id)
    {
        $folder = ClientFolder::findOrFail($id);

        // Empêcher la suppression des dossiers système
        if ($folder->is_system) {
            return response()->json(['message' => 'Impossible de supprimer un dossier système'], 403);
        }

        $folder->delete();

        return response()->json(['message' => 'Dossier supprimé']);
    }

    // ─── API ────────────────────────────────────────────────────

    /**
     * API: Liste des dossiers pour un client (arborescence).
     */
    public function listAll($clientId)
    {
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
