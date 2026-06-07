<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return view('shop', [
            'products' => Product::all()
        ]);
    }

    public function show($id)
    {
        return view('product-detail', [
            'product' => Product::findOrFail($id)
        ]);
    }

    public function home()
    {
        return view('home', [
            'bestSellers' => Product::take(4)->get()
        ]);
    }

    public function apiShow($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json($product);
    }
}
