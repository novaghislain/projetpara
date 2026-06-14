<?php

namespace App\Http\Controllers\Catalogue;

use App\Http\Controllers\Controller;
use App\Models\CatalogueCategory;
use App\Models\CatalogueService;
use Illuminate\Http\Request;


class AdminCatalogueController extends Controller
{
    /**
     * Gestion du catalogue (catégories + services)
     */
    public function index()
    {
        $categories = CatalogueCategory::with(['services' => function ($q) {
            $q->orderBy('ordre_affichage');
        }])->orderBy('ordre')->get();

        return view('app', [
            'page' => 'admin-services-index',
            'props' => [
                'categories' => $categories,
            ]
        ]);
    }

    // ── Catégories ──────────────────────────────────────────────────────────

    public function storeCategory(Request $request)
    {
        $request->validate([
            'nom'         => 'required|string|max:255',
            'icone'       => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'couleur'     => 'nullable|string|max:100',
            'ordre'       => 'nullable|integer',
        ]);

        CatalogueCategory::create($request->all());

        return redirect()->back()->with('success', 'Catégorie créée.');
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'nom'    => 'required|string|max:255',
            'icone'  => 'nullable|string|max:100',
            'actif'  => 'boolean',
            'ordre'  => 'nullable|integer',
        ]);

        CatalogueCategory::findOrFail($id)->update($request->all());
        return redirect()->back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory($id)
    {
        CatalogueCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Catégorie supprimée.');
    }

    // ── Services ────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'category_id'           => 'required|exists:catalogue_categories,id',
            'nom'                   => 'required|string|max:255',
            'description'           => 'nullable|string',
            'inclus_json'           => 'nullable|array',
            'delai_jours'           => 'nullable|string',
            'tarif_fcfa'            => 'nullable|numeric',
            'tarif_type'            => 'required|in:fixe,devis',
            'documents_requis_json' => 'nullable|array',
            'champs_formulaire_json'=> 'nullable|array',
            'ordre_affichage'       => 'nullable|integer',
        ]);

        CatalogueService::create($request->all());
        return redirect()->back()->with('success', 'Service créé.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id'           => 'required|exists:catalogue_categories,id',
            'nom'                   => 'required|string|max:255',
            'tarif_type'            => 'required|in:fixe,devis',
            'actif'                 => 'boolean',
        ]);

        CatalogueService::findOrFail($id)->update($request->all());
        return redirect()->back()->with('success', 'Service mis à jour.');
    }

    public function destroy($id)
    {
        CatalogueService::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Service supprimé.');
    }
}
