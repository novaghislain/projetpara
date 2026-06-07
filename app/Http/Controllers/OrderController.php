<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer' => 'required|array',
            'items' => 'required|array',
            'total' => 'required|integer',
            'paymentMethod' => 'required|string',
            'paymentReference' => 'nullable|string',
        ]);

        $order = Order::create([
            'user_id' => Auth::id(),
            'customer_firstName' => $validated['customer']['firstName'],
            'customer_lastName' => $validated['customer']['lastName'],
            'customer_phone' => $validated['customer']['phone'],
            'customer_city' => $validated['customer']['city'],
            'customer_address' => $validated['customer']['address'],
            'total' => $validated['total'],
            'items' => $validated['items'],
            'paymentMethod' => $validated['paymentMethod'],
            'payment_reference' => $validated['paymentReference'] ?? null,
        ]);

        return response()->json($order, 201);
    }
}
