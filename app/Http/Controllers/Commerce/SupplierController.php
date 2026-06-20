<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index()
    {
        return view('app', ['page' => 'commerce-suppliers']);
    }

    public function listAll()
    {
        $user = Auth::user();
        $query = Supplier::withCount('products');

        if ($user->client_id) {
            $query->where('client_id', $user->client_id);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'delivery_delay' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['client_id'] = Auth::user()->client_id ?? Auth::id();
        $supplier = Supplier::create($validated);

        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        $supplier = Supplier::with('products')->findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'delivery_delay' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);
        return response()->json($supplier);
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return response()->json(['message' => 'Fournisseur supprimé']);
    }
}
