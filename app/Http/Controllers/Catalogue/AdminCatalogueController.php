<?php

namespace App\Http\Controllers\Catalogue;

use App\Http\Controllers\Controller;
use App\Models\CatalogueCategory;
use App\Models\CatalogueService;
use Illuminate\Http\Request;


class AdminCatalogueController extends Controller
{
    /**
     * Contrôleur pour la gestion administrative du catalogue.
     * Permet de gérer les catégories et les services du catalogue
     * (création, modification, suppression).
     */

    /**
     * Affiche la liste complète des catégories avec leurs services.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $categories = CatalogueCategory::with([
            'services' => function ($q) {
                $q->orderBy('type')->orderBy('ordre_affichage');
            }
        ])->orderBy('ordre')->get();

        return view('app', [
            'page' => 'admin-services-index',
            'props' => [
                'categories' => $categories,
            ]
        ]);
    }

    // ── Catégories ──────────────────────────────────────────────────────────

    /**
     * Crée une nouvelle catégorie dans le catalogue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Met à jour une catégorie existante.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id Identifiant de la catégorie
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Supprime une catégorie du catalogue.
     *
     * @param int $id Identifiant de la catégorie
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyCategory($id)
    {
        CatalogueCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Catégorie supprimée.');
    }

    // ── Services ────────────────────────────────────────────────────────────

    /**
     * Crée un nouveau service dans une catégorie.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id'           => 'required|exists:catalogue_categories,id',
            'nom'                   => 'required|string|max:255',
            'type'                  => 'nullable|string|in:service,modele',
            'description'           => 'nullable|string',
            'inclus_json'           => 'nullable|array',
            'delai_jours'           => 'nullable|string',
            'tarif_fcfa'            => 'nullable|numeric',
            'tarif_type'            => 'required|in:fixe,devis',
            'documents_requis_json' => 'nullable|array',
            'champs_formulaire_json'=> 'nullable|array',
            'ordre_affichage'       => 'nullable|integer',
        ]);

        $data = $request->all();
        if (!isset($data['type'])) {
            $data['type'] = 'service';
        }
        CatalogueService::create($data);
        return redirect()->back()->with('success', 'Service créé.');
    }

    /**
     * Met à jour un service existant.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id Identifiant du service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id'           => 'required|exists:catalogue_categories,id',
            'nom'                   => 'required|string|max:255',
            'type'                  => 'nullable|string|in:service,modele',
            'tarif_type'            => 'required|in:fixe,devis',
            'actif'                 => 'boolean',
        ]);

        CatalogueService::findOrFail($id)->update($request->all());
        return redirect()->back()->with('success', 'Service mis à jour.');
    }

    /**
     * Supprime un service du catalogue.
     *
     * @param int $id Identifiant du service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        CatalogueService::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Service supprimé.');
    }
}
