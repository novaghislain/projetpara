<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        return view('app', ['page' => 'commerce-categories']);
    }

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

    public function show($id)
    {
        $category = ProductCategory::with('children', 'parent')->findOrFail($id);
        return response()->json($category);
    }

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

    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Catégorie supprimée']);
    }
}
