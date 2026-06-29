<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeDocumentDossier;
use Illuminate\Http\Request;

class DaeDocumentDossiersController extends BaseDaeController
{
    /**
     * Retourne l'arbre complet des dossiers pour la sidebar.
     */
    public function arbre(Request $request)
    {
        $query = DaeDocumentDossier::with(['children' => function ($q) {
            $q->orderBy('ordre')->orderBy('nom');
        }])->whereNull('parent_id')->orderBy('ordre')->orderBy('nom');

        $query->where('client_id', $this->getClientId($request));

        $dossiers = $query->get()->map(fn($d) => $this->formatNoeud($d));

        return response()->json($dossiers);
    }

    /**
     * Crée un nouveau dossier.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'        => 'required|string|max:255',
            'parent_id'  => 'nullable|exists:dae_document_dossiers,id',
            'couleur'    => 'nullable|string|max:20',
            'description'=> 'nullable|string|max:500',
            'ordre'      => 'nullable|integer|min:0',
        ]);

        $dossier = DaeDocumentDossier::create($validated + ['client_id' => $this->getClientId($request)]);

        return response()->json($dossier, 201);
    }

    /**
     * Met à jour un dossier (nom, couleur, déplacement).
     */
    public function update(Request $request, $id)
    {
        $dossier = DaeDocumentDossier::findOrFail($id);

        $validated = $request->validate([
            'nom'        => 'sometimes|string|max:255',
            'parent_id'  => 'nullable|exists:dae_document_dossiers,id',
            'couleur'    => 'nullable|string|max:20',
            'description'=> 'nullable|string|max:500',
            'ordre'      => 'nullable|integer|min:0',
        ]);

        if (isset($validated['parent_id']) && $validated['parent_id'] == $id) {
            return response()->json(['message' => 'Un dossier ne peut pas être son propre parent.'], 422);
        }

        $dossier->update($validated);

        return response()->json($dossier);
    }

    /**
     * Supprime un dossier.
     * Les sous-dossiers remontent d'un niveau, les documents perdent leur dossier.
     */
    public function destroy($id)
    {
        $dossier = DaeDocumentDossier::findOrFail($id);

        // Remonter les sous-dossiers
        $dossier->children()->update(['parent_id' => $dossier->parent_id]);

        // Détacher les documents
        $dossier->documents()->update(['dossier_id' => null]);

        $dossier->delete();

        return response()->json(['message' => 'Dossier supprimé.']);
    }

    /**
     * Formate un dossier avec ses enfants récursivement.
     */
    private function formatNoeud(DaeDocumentDossier $dossier): array
    {
        $enfants = $dossier->children()
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get()
            ->map(fn($e) => $this->formatNoeud($e))
            ->toArray();

        return [
            'id'          => $dossier->id,
            'nom'         => $dossier->nom,
            'couleur'     => $dossier->couleur,
            'parent_id'   => $dossier->parent_id,
            'description' => $dossier->description,
            'ordre'       => $dossier->ordre,
            'document_count' => $dossier->documents()->count(),
            'enfants'     => $enfants,
            'created_at'  => $dossier->created_at,
        ];
    }
}
