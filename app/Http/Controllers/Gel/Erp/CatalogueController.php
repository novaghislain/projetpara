<?php

namespace App\Http\Controllers\Gel\Erp;

use App\Http\Controllers\Controller;
use App\Models\ErpCategory;
use App\Models\ErpItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CatalogueController extends Controller
{
    /**
     * Contrôleur de gestion du catalogue ERP.
     * Permet de créer et gérer les catégories et les articles
     * du catalogue de produits/services dans le module ERP.
     */
    public function storeCategory(Request $request)
    {
        /**
         * Crée une nouvelle catégorie dans le catalogue.
         *
         * POST /erp/catalogue/categories
         *
         * @param Request $request La requête HTTP contenant les données de la catégorie
         * @return \Illuminate\Http\JsonResponse La réponse JSON avec la catégorie créée
         */
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        // Validation des données d'entrée
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Création de la catégorie en base de données
        $category = ErpCategory::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data'    => $category,
        ], 201);
    }

    /**
     * Crée un nouvel article dans le catalogue.
     *
     * POST /erp/catalogue/items
     *
     * @param Request $request La requête HTTP contenant les données de l'article
     * @return \Illuminate\Http\JsonResponse La réponse JSON avec l'article créé
     */
    public function storeItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'erp_category_id' => 'required|integer|exists:erp_categories,id',
            'reference'       => 'required|string|max:255|unique:erp_items,reference',
            'designation'     => 'required|string|max:255',
            'purchase_price'  => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'stock_alert'     => 'nullable|integer|min:0',
            'unit'            => 'required|string|max:50',
        ]);

        // Validation des données d'entrée
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Création de l'article avec chargement de sa catégorie associée
        $item = ErpItem::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Item created successfully.',
            'data'    => $item->load('category'),
        ], 201);
    }
}
