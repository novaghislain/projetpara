<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get the current cart from session.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $count = array_sum(array_column($cart, 'quantity'));
        $total = array_reduce($cart, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        return response()->json([
            'items' => array_values($cart),
            'count' => $count,
            'total' => $total,
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:products,id',
            'quantity' => 'integer|min:1|max:99',
        ]);

        $product = Product::findOrFail($request->id);
        $cart = session()->get('cart', []);
        $productId = $product->id;

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $request->quantity ?? 1;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'img' => $product->img,
                'quantity' => $request->quantity ?? 1,
            ];
        }

        session()->put('cart', $cart);

        return $this->index();
    }

    /**
     * Update quantity of a cart item.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'quantity' => 'required|integer|min:0|max:99',
        ]);

        $cart = session()->get('cart', []);

        if ($request->quantity <= 0) {
            unset($cart[$request->id]);
        } elseif (isset($cart[$request->id])) {
            $cart[$request->id]['quantity'] = $request->quantity;
        }

        session()->put('cart', $cart);

        return $this->index();
    }

    /**
     * Remove an item from the cart.
     */
    public function remove($id)
    {
        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session()->put('cart', $cart);

        return $this->index();
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        session()->forget('cart');

        return $this->index();
    }
}
