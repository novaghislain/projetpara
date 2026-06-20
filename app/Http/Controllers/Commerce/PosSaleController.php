<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\PosSession;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\BusinessUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosSaleController extends Controller
{
    public function index()
    {
        return view('app', ['page' => 'commerce-pos']);
    }

    // ── Sessions de caisse ──────────────────────────────────────

    public function openSession(Request $request)
    {
        $clientId = Auth::user()->client_id ?? Auth::id();
        $businessUser = BusinessUser::where('client_id', $clientId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Vérifier pas de session ouverte
        $openSession = PosSession::where('client_id', $clientId)
            ->where('business_user_id', $businessUser->id)
            ->where('status', 'open')
            ->first();

        if ($openSession) {
            return response()->json([
                'message' => 'Une session est déjà ouverte',
                'session' => $openSession,
            ], 422);
        }

        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $session = PosSession::create([
            'client_id' => $clientId,
            'business_user_id' => $businessUser->id,
            'opening_amount' => $validated['opening_amount'],
            'status' => 'open',
        ]);

        return response()->json($session, 201);
    }

    public function closeSession(Request $request, $id)
    {
        $session = PosSession::findOrFail($id);

        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $totalSales = Sale::where('pos_session_id', $session->id)
            ->where('status', 'completed')
            ->sum('total_ttc');

        $expected = $session->opening_amount + $totalSales;
        $difference = $validated['closing_amount'] - $expected;

        $session->update([
            'closing_amount' => $validated['closing_amount'],
            'expected_amount' => $expected,
            'difference' => $difference,
            'closed_at' => now(),
            'status' => 'closed',
            'notes' => $validated['notes'] ?? $session->notes,
        ]);

        return response()->json($session);
    }

    public function currentSession()
    {
        $clientId = Auth::user()->client_id ?? Auth::id();
        $businessUser = BusinessUser::where('client_id', $clientId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$businessUser) {
            return response()->json(null);
        }

        $session = PosSession::with('sales')
            ->where('client_id', $clientId)
            ->where('business_user_id', $businessUser->id)
            ->where('status', 'open')
            ->first();

        if ($session) {
            $session->loadCount(['sales as total_sales' => function ($q) {
                $q->where('status', 'completed');
            }]);
            $session->total_amount = Sale::where('pos_session_id', $session->id)
                ->where('status', 'completed')
                ->sum('total_ttc');
        }

        return response()->json($session);
    }

    public function sessions()
    {
        $clientId = Auth::user()->client_id ?? Auth::id();
        $sessions = PosSession::with('businessUser.user')
            ->where('client_id', $clientId)
            ->latest()
            ->paginate(20);

        return response()->json($sessions);
    }

    // ── Ventes ──────────────────────────────────────────────────

    public function sell(Request $request)
    {
        $clientId = Auth::user()->client_id ?? Auth::id();
        $businessUser = BusinessUser::where('client_id', $clientId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'pos_session_id' => 'required|exists:pos_sessions,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.discount' => 'nullable|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method' => 'required|in:especes,momo,moov,carte,autre',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.reference' => 'nullable|string',
        ]);

        $sale = DB::transaction(function () use ($validated, $clientId, $businessUser) {
            $items = [];
            $subtotal = 0;
            $totalTax = 0;

            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                // Vérifier le stock
                if ($product->stock_qty < $itemData['quantity']) {
                    throw new \Exception("Stock insuffisant pour {$product->name}: {$product->stock_qty} disponible(s)");
                }

                $qty = $itemData['quantity'];
                $priceHT = $product->price_ht;
                $priceTTC = $product->price_ttc;
                $itemDiscount = $itemData['discount'] ?? 0;
                $itemTotalHT = ($priceHT * $qty) - $itemDiscount;
                $itemTotalTTC = ($priceTTC * $qty) - $itemDiscount;

                $items[] = [
                    'product_id' => $product->id,
                    'variant_id' => $itemData['variant_id'] ?? null,
                    'product_name' => $product->name,
                    'barcode' => $product->barcode,
                    'quantity' => $qty,
                    'unit_price_ht' => $priceHT,
                    'unit_price_ttc' => $priceTTC,
                    'discount' => $itemDiscount,
                    'total_ht' => $itemTotalHT,
                    'total_ttc' => $itemTotalTTC,
                    'tva_rate' => $product->tva_rate,
                ];

                $subtotal += $priceHT * $qty;
                $totalTax += ($priceTTC - $priceHT) * $qty;

                // Déduire le stock
                $before = $product->stock_qty;
                $after = $before - $qty;
                $product->update(['stock_qty' => $after]);

                StockMovement::create([
                    'client_id' => $clientId,
                    'product_id' => $product->id,
                    'variant_id' => $itemData['variant_id'] ?? null,
                    'type' => 'exit',
                    'quantity' => $qty,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'reference_type' => 'sale',
                    'motif' => 'Vente POS',
                    'created_by' => Auth::id(),
                ]);
            }

            $discount = $validated['discount'] ?? 0;
            $discountType = $validated['discount_type'] ?? 'fixed';

            if ($discountType === 'percentage') {
                $discountAmount = $subtotal * ($discount / 100);
            } else {
                $discountAmount = min($discount, $subtotal);
            }

            $totalHT = $subtotal - $discountAmount;
            $totalTTC = $totalHT + $totalTax;

            // Générer la référence
            $ref = 'VNT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $sale = Sale::create([
                'reference' => $ref,
                'client_id' => $clientId,
                'pos_session_id' => $validated['pos_session_id'],
                'business_user_id' => $businessUser->id,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'discount_type' => $discountType,
                'total_ht' => $totalHT,
                'total_ttc' => $totalTTC,
                'tax_amount' => $totalTax,
                'paid_amount' => collect($validated['payments'])->sum('amount'),
                'change_amount' => max(0, collect($validated['payments'])->sum('amount') - $totalTTC),
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Créer les lignes
            foreach ($items as $item) {
                $sale->items()->create($item);
            }

            // Créer les paiements
            foreach ($validated['payments'] as $payment) {
                $sale->payments()->create([
                    'payment_method' => $payment['payment_method'],
                    'amount' => $payment['amount'],
                    'reference' => $payment['reference'] ?? null,
                    'status' => 'completed',
                ]);
            }

            return $sale;
        });

        return response()->json(
            $sale->load(['items.product', 'payments', 'businessUser.user']),
            201
        );
    }

    public function returnSale(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        if ($sale->status !== 'completed') {
            return response()->json(['message' => 'Cette vente ne peut pas être retournée'], 422);
        }

        $validated = $request->validate([
            'motif' => 'required|string|max:255',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($sale, $validated) {
            $itemsToReturn = $validated['items'] ?? null;

            foreach ($sale->items as $item) {
                $qtyToReturn = $itemsToReturn
                    ? (collect($itemsToReturn)->firstWhere('product_id', $item->product_id)['quantity'] ?? 0)
                    : $item->quantity;

                if ($qtyToReturn <= 0) continue;

                $product = Product::find($item->product_id);
                if ($product) {
                    $before = $product->stock_qty;
                    $after = $before + $qtyToReturn;
                    $product->update(['stock_qty' => $after]);

                    StockMovement::create([
                        'client_id' => $sale->client_id,
                        'product_id' => $product->id,
                        'type' => 'entry',
                        'quantity' => $qtyToReturn,
                        'stock_before' => $before,
                        'stock_after' => $after,
                        'reference_type' => 'return',
                        'reference_id' => $sale->id,
                        'motif' => 'Retour: ' . $validated['motif'],
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            $sale->update(['status' => 'returned', 'notes' => $sale->notes . "\nRetour: " . $validated['motif']]);
        });

        return response()->json(['message' => 'Retour effectué', 'sale' => $sale->fresh()]);
    }

    public function receipt($id)
    {
        $sale = Sale::with(['items', 'payments', 'businessUser.user', 'client'])
            ->findOrFail($id);

        return response()->json($sale);
    }

    public function salesList(Request $request)
    {
        $clientId = Auth::user()->client_id ?? Auth::id();
        $query = Sale::with(['items.product', 'payments', 'businessUser.user']);

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->status) $query->where('status', $request->status);
        if ($request->payment_method) {
            $query->whereHas('payments', fn($q) => $q->where('payment_method', $request->payment_method));
        }

        return response()->json($query->latest()->paginate($request->per_page ?? 20));
    }
}
