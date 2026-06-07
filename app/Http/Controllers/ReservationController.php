<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Show reservation form for a specific product.
     */
    public function create($productId)
    {
        $product = Product::findOrFail($productId);

        return view('reservation', [
            'product' => $product,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Store a new reservation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'customer_firstName' => 'required|string|max:255',
            'customer_lastName' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_city' => 'required|string|max:255',
            'customer_address' => 'nullable|string|max:500',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'quantity' => 'integer|min:1|max:99',
        ]);

        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'product_id' => $validated['product_id'],
            'customer_firstName' => $validated['customer_firstName'],
            'customer_lastName' => $validated['customer_lastName'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'customer_city' => $validated['customer_city'],
            'customer_address' => $validated['customer_address'],
            'reservation_date' => $validated['reservation_date'],
            'reservation_time' => $validated['reservation_time'],
            'notes' => $validated['notes'],
            'quantity' => $validated['quantity'] ?? 1,
            'status' => 'pending',
        ]);

        return response()->json($reservation, 201);
    }

    /**
     * List reservations for the authenticated user.
     */
    public function index()
    {
        $reservations = Reservation::with('product')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reservations);
    }

    /**
     * List all reservations (admin).
     */
    public function adminIndex()
    {
        $reservations = Reservation::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reservations);
    }

    /**
     * Update reservation status (admin).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed,expired',
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->status = $request->status;

        if ($request->status === 'confirmed' && !$reservation->confirmed_at) {
            $reservation->confirmed_at = now();
        }

        $reservation->save();

        return response()->json($reservation);
    }
}
