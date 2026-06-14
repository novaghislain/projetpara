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
     * Store a new warehouse.
     *
     * POST /erp/stocks/warehouses
     */
    public function storeWarehouse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'location'  => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

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
     * Store a new stock movement.
     *
     * POST /erp/stocks/movements
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (!isset($data['created_by'])) {
            $data['created_by'] = $request->user()?->id;
        }

        $movement = ErpStockMovement::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Stock movement recorded successfully.',
            'data'    => $movement->load(['item', 'warehouse']),
        ], 201);
    }
}
