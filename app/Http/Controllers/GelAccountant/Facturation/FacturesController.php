<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Partner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Services\EmecefService;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Contrôleur de facturation pour GEL Accountant.
 * Gère la liste, la création, l'édition et la suppression des factures.
 */
class FacturesController extends Controller
{
    /**
     * Liste des factures du client actif.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $query = Invoice::where('client_id', $clientId)
            ->where('type', 'customer_invoice')
            ->with(['partner', 'lines']);

        // Filtrage par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderByDesc('invoice_date')->paginate(20);

        // Statistiques rapides
        $stats = [
            'total' => Invoice::where('client_id', $clientId)->where('type', 'customer_invoice')->count(),
            'draft' => Invoice::where('client_id', $clientId)->where('type', 'customer_invoice')->where('status', 'draft')->count(),
            'sent' => Invoice::where('client_id', $clientId)->where('type', 'customer_invoice')->where('status', 'sent')->count(),
            'overdue' => Invoice::where('client_id', $clientId)->where('type', 'customer_invoice')->where('status', 'overdue')->count(),
            'paid' => Invoice::where('client_id', $clientId)->where('type', 'customer_invoice')->where('status', 'paid')->count(),
            'total_amount' => Invoice::where('client_id', $clientId)->where('type', 'customer_invoice')->sum('total'),
            'total_due' => Invoice::where('client_id', $clientId)->where('type', 'customer_invoice')->whereNotIn('status', ['paid', 'cancelled'])->sum('balance_due'),
        ];

        return view('gel-accountant.factures.index', compact('invoices', 'stats'));
    }

    /**
     * Formulaire de création d'une facture.
     */
    public function create()
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $partners = Partner::where('client_id', $clientId)
            ->whereIn('type', ['customer', 'both'])
            ->where('status', 'actif')
            ->orderBy('company_name')
            ->get();

        $products = Product::where('client_id', $clientId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('gel-accountant.factures.create', compact('partners', 'products'));
    }

    /**
     * Enregistre une nouvelle facture.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'payment_term' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.vat_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;
        $partner = Partner::findOrFail($validated['partner_id']);

        return DB::transaction(function () use ($validated, $user, $clientId, $partner) {
            // Générer le numéro de facture
            $lastInvoice = Invoice::where('client_id', $clientId)
                ->where('type', 'customer_invoice')
                ->orderByDesc('id')
                ->first();

            $nextNum = $lastInvoice
                ? intval(preg_replace('/[^0-9]/', '', substr($lastInvoice->invoice_number, -4))) + 1
                : 1;

            $invoiceNumber = 'FAC-' . date('Y') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // Calculer les totaux
            $subtotal = 0;
            $vatTotal = 0;

            foreach ($validated['lines'] as $line) {
                $lineSubtotal = $line['quantity'] * $line['unit_price'];
                $lineVat = $lineSubtotal * (($line['vat_rate'] ?? 0) / 100);
                $subtotal += $lineSubtotal;
                $vatTotal += $lineVat;
            }

            $total = $subtotal + $vatTotal;

            $invoice = Invoice::create([
                'client_id' => $clientId,
                'type' => 'customer_invoice',
                'invoice_number' => $invoiceNumber,
                'partner_id' => $partner->id,
                'partner_name' => $partner->company_name ?? ($partner->last_name . ' ' . $partner->first_name),
                'partner_tax_id' => $partner->tax_id,
                'partner_address' => $partner->address,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'payment_term' => $validated['payment_term'] ?? null,
                'status' => 'draft',
                'currency' => 'XOF',
                'exchange_rate' => 1,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax_base' => $subtotal,
                'vat_total' => $vatTotal,
                'total' => $total,
                'paid_amount' => 0,
                'balance_due' => $total,
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'created_by' => $user->id,
            ]);

            // Créer les lignes
            foreach ($validated['lines'] as $i => $line) {
                $lineSubtotal = $line['quantity'] * $line['unit_price'];
                $vatRate = $line['vat_rate'] ?? 0;
                $lineVat = $lineSubtotal * ($vatRate / 100);

                InvoiceLine::create([
                    'client_id' => $clientId,
                    'invoice_id' => $invoice->id,
                    'line_number' => $i + 1,
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'] ?? 'pièce',
                    'unit_price' => $line['unit_price'],
                    'discount' => 0,
                    'net_unit_price' => $line['unit_price'],
                    'subtotal' => $lineSubtotal,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $lineVat,
                    'total' => $lineSubtotal + $lineVat,
                ]);
            }

            return redirect()->route('gel-accountant.factures.index')
                ->with('success', "Facture {$invoiceNumber} créée avec succès !");
        });
    }

    /**
     * Affiche le détail d'une facture.
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $invoice = Invoice::where('client_id', $clientId)
            ->with(['lines', 'partner', 'payments'])
            ->findOrFail($id);

        $qrCodeBase64 = null;
        if ($invoice->emecef_statut === 'emise' && !empty($invoice->emecef_qr)) {
            $options = new QROptions([
                'version'      => QRCode::VERSION_AUTO,
                'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'     => QRCode::ECC_L,
                'imageBase64'  => true,
            ]);
            $qrcode = new QRCode($options);
            $qrCodeBase64 = $qrcode->render($invoice->emecef_qr);
        }

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('gel-accountant.factures.pdf', compact('invoice', 'qrCodeBase64'));
            return $pdf->stream('facture_' . $invoice->invoice_number . '.pdf');
        }

        return view('gel-accountant.factures.show', compact('invoice', 'qrCodeBase64'));
    }

    /**
     * Certifie une facture auprès de l'API e-MECeF.
     */
    public function certify($id, EmecefService $emecefService)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $invoice = Invoice::where('client_id', $clientId)->findOrFail($id);

        if ($invoice->emecef_statut === 'emise') {
            return back()->with('info', 'Cette facture est déjà certifiée.');
        }

        $result = $emecefService->emettreFactureNormalisee($invoice);

        if ($result['success']) {
            return back()->with('success', 'Facture certifiée avec succès ! NIM: ' . ($result['nim'] ?? ''));
        } else {
            return back()->with('error', 'Erreur de certification e-MECeF : ' . ($result['error'] ?? 'Erreur inconnue'));
        }
    }

    /**
     * Supprime une facture (brouillon uniquement).
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $invoice = Invoice::where('client_id', $clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $invoice->lines()->delete();
        $invoice->delete();

        return redirect()->route('gel-accountant.factures.index')
            ->with('success', "Facture {$invoice->invoice_number} supprimée.");
    }
}
