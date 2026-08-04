<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Contrôleur pour la gestion des catégories de produits.
     * Permet de créer, modifier, consulter et supprimer les catégories
     * avec une structure hiérarchique (parent/enfant).
     */

    /**
     * Affiche la page de gestion des catégories de produits.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('app', ['page' => 'commerce-categories']);
    }

    /**
     * Retourne la liste de toutes les catégories avec leurs enfants.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAll()
    {
        $user = Auth::user();
        $query = ProductCategory::with('children');

        if ($user->client_id) {
            $query->where('client_id', $user->client_id);
        } elseif (!in_array($user->role, ['super_admin', 'director'])) {
            $query->where('client_id', $user->client_id);
        }

        return response()->json($query->latest()->get());
    }

    /**
     * Crée une nouvelle catégorie de produit.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:product_categories,id',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['client_id'] = Auth::user()->client_id ?? Auth::id();
        $category = ProductCategory::create($validated);

        return response()->json($category->load('children'), 201);
    }

    /**
     * Affiche les détails d'une catégorie avec ses enfants et son parent.
     *
     * @param int $id Identifiant de la catégorie
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $category = ProductCategory::with('children', 'parent')->findOrFail($id);
        return response()->json($category);
    }

    /**
     * Met à jour une catégorie existante.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id Identifiant de la catégorie
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:product_categories,id',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);
        return response()->json($category->load('children'));
    }

    /**
     * Supprime une catégorie.
     *
     * @param int $id Identifiant de la catégorie
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Catégorie supprimée']);
    }
}
