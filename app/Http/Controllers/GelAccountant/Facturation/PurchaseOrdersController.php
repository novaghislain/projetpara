<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrdersController extends Controller
{
    /**
     * Liste des bons de commande.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $query = Invoice::where('client_id', $clientId)
            ->where('type', 'purchase_order')
            ->with('partner');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $purchaseOrders = $query->orderByDesc('invoice_date')->paginate(20);

        return view('gel-accountant.purchase-orders.index', compact('purchaseOrders'));
    }

    /**
     * Formulaire de création d'un bon de commande.
     */
    public function create()
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $partners = Partner::where('client_id', $clientId)
            ->whereIn('type', ['fournisseur', 'mixte'])
            ->get();

        return view('gel-accountant.purchase-orders.create', compact('partners'));
    }

    /**
     * Enregistre un nouveau bon de commande.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'reference' => 'nullable|string|max:255',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $partner = Partner::findOrFail($validated['partner_id']);

        return DB::transaction(function () use ($validated, $clientId, $partner) {
            $po = Invoice::create([
                'client_id' => $clientId,
                'type' => 'purchase_order',
                'partner_id' => $partner->id,
                'partner_name' => $partner->company_name ?: ($partner->first_name . ' ' . $partner->last_name),
                'invoice_date' => $validated['order_date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'due_date' => $validated['order_date'], // Not strictly due
                'status' => 'draft',
                'notes' => $validated['notes'],
                'terms_conditions' => $validated['reference'],
            ]);

            // Auto-générer le numéro (ex: BDC-2026-0001)
            $po->invoice_number = 'BDC-' . date('Y') . '-' . str_pad($po->id, 4, '0', STR_PAD_LEFT);
            $po->save();

            $subtotal = 0;
            $taxTotal = 0;

            foreach ($validated['lines'] as $line) {
                $qty = $line['quantity'];
                $price = $line['unit_price'];
                $taxRate = $line['tax_rate'] ?? 0;
                
                $lineSubtotal = $qty * $price;
                $lineTax = $lineSubtotal * ($taxRate / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                InvoiceLine::create([
                    'invoice_id' => $po->id,
                    'description' => $line['description'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'vat_rate' => $taxRate,
                    'subtotal' => $lineSubtotal,
                    'vat_amount' => $lineTax,
                    'total' => $lineTotal,
                ]);

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;
            }

            $po->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxTotal,
                'total' => $subtotal + $taxTotal,
                'balance_due' => $subtotal + $taxTotal,
            ]);

            return redirect()->route('gel-accountant.purchase-orders.show', $po->id)
                ->with('success', 'Bon de commande créé avec succès.');
        });
    }

    /**
     * Affiche un bon de commande.
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $po = Invoice::where('client_id', $clientId)
            ->where('type', 'purchase_order')
            ->with(['lines', 'partner'])
            ->findOrFail($id);

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('gel-accountant.purchase-orders.pdf', compact('po'));
            return $pdf->stream('commande_' . $po->invoice_number . '.pdf');
        }

        return view('gel-accountant.purchase-orders.show', compact('po'));
    }

    /**
     * Confirme l'envoi du bon de commande (change statut).
     */
    public function send($id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $po = Invoice::where('client_id', $clientId)
            ->where('type', 'purchase_order')
            ->findOrFail($id);
            
        $po->update(['status' => 'sent']);

        return back()->with('success', 'Le bon de commande a été marqué comme envoyé.');
    }
}
