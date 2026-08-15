<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\B2b\Supplier;
use App\Models\B2b\PurchaseOrder;

class SupplierController extends Controller
{
    public function createSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'tax_number' => 'nullable|string'
        ]);

        $supplier = Supplier::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $supplier,
            'message' => 'Fournisseur ajouté.'
        ]);
    }

    public function createPurchaseOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'client_id' => 'required|uuid',
            'order_number' => 'required|string|unique:purchase_orders',
            'total_amount' => 'required|numeric|min:0'
        ]);

        $order = PurchaseOrder::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $order,
            'message' => 'Bon de commande créé.'
        ]);
    }
}
