<?php

namespace App\Http\Controllers\Api\Invoicing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoicing\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Services\Invoicing\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Liste paginée des factures.
     */
    public function index(Request $request): JsonResponse
    {
        $clientId = $this->getClientId();

        $query = Invoice::with(['partner', 'lines'])
            ->where('client_id', $clientId);

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($invoices);
    }

    /**
     * Détail d'une facture.
     */
    public function show(string $id): JsonResponse
    {
        $invoice = Invoice::with(['lines', 'partner', 'payments', 'journalEntry', 'creditNotes'])
            ->where('id', $id)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        return response()->json([
            'data' => $invoice,
        ]);
    }

    /**
     * Crée une facture.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->createInvoice($request->validated());

        return response()->json([
            'data' => $invoice,
            'message' => 'Facture créée avec succès.',
        ], 201);
    }

    /**
     * Valide une facture (brouillon → envoyé) et génère l'écriture comptable.
     */
    public function validate(string $id): JsonResponse
    {
        $invoice = $this->invoiceService->validateInvoice($id);

        return response()->json([
            'data' => $invoice,
            'message' => 'Facture validée et écriture comptable générée.',
        ]);
    }

    /**
     * Enregistre un paiement sur une facture.
     */
    public function pay(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'bank_account_id' => 'nullable|integer|exists:accounting_accounts,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoice = $this->invoiceService->recordPayment($id, $validated);

        return response()->json([
            'data' => $invoice,
            'message' => 'Paiement enregistré avec succès.',
        ]);
    }

    /**
     * Annule une facture.
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $invoice = $this->invoiceService->cancelInvoice($id, $validated['reason'] ?? null);

        return response()->json([
            'data' => $invoice,
            'message' => 'Facture annulée.',
        ]);
    }
}
