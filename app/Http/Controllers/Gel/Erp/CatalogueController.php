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
     * Store a new category.
     *
     * POST /erp/catalogue/categories
     */
    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $category = ErpCategory::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data'    => $category,
        ], 201);
    }

    /**
     * Store a new item.
     *
     * POST /erp/catalogue/items
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $item = ErpItem::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Item created successfully.',
            'data'    => $item->load('category'),
        ], 201);
    }
}
