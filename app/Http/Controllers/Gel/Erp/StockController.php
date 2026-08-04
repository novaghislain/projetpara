<?php

namespace App\Http\Controllers\Gel\Erp;

use App\Http\Controllers\Controller;
use App\Models\ErpWarehouse;
use App\Models\ErpStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    /**
     * Contrôleur de gestion des stocks dans le module ERP.
     * Permet la gestion des entrepôts et des mouvements de stock
     * (entrées et sorties).
     */
    public function storeWarehouse(Request $request)
    {
        /**
         * Crée un nouvel entrepôt dans le système de stock.
         *
         * POST /erp/stocks/warehouses
         *
         * @param Request $request La requête HTTP contenant les données de l'entrepôt
         * @return \Illuminate\Http\JsonResponse La réponse JSON avec l'entrepôt créé
         */
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'location'  => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        // Validation des données d'entrée
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Actif par défaut si non précisé
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        $warehouse = ErpWarehouse::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully.',
            'data'    => $warehouse,
        ], 201);
    }

    /**
     * Enregistre un mouvement de stock (entrée ou sortie).
     *
     * POST /erp/stocks/movements
     *
     * @param Request $request La requête HTTP contenant les données du mouvement
     * @return \Illuminate\Http\JsonResponse La réponse JSON avec le mouvement créé
     */
    public function storeMovement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'erp_item_id'      => 'required|integer|exists:erp_items,id',
            'erp_warehouse_id' => 'required|integer|exists:erp_warehouses,id',
            'type'             => 'required|string|in:entry,exit',
            'quantity'         => 'required|numeric|min:0.01',
            'reference_doc'    => 'nullable|string|max:255',
            'movement_date'    => 'required|date',
            'motif'            => 'nullable|string|max:1000',
            'created_by'       => 'nullable|integer|exists:users,id',
        ]);

        // Validation des données d'entrée
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Attribution de l'utilisateur connecté comme créateur si non spécifié
        if (!isset($data['created_by'])) {
            $data['created_by'] = $request->user()?->id;
        }

        $movement = ErpStockMovement::create($data);

        // Chargement des relations article et entrepôt pour la réponse
        return response()->json([
            'success' => true,
            'message' => 'Stock movement recorded successfully.',
            'data'    => $movement->load(['item', 'warehouse']),
        ], 201);
    }
}
