<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyInvoice;
use App\Models\CompanyInvoiceItem;
use App\Models\CompanyPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Recupere le client_id de l'utilisateur authentifie.
     */
    private function getClientId(): int
    {
        $user = Auth::user();
        if (!$user->client_id) {
            abort(403, 'Aucune entreprise associee.');
        }
        return (int) $user->client_id;
    }

    /**
     * Verifie que l'utilisateur est bien administrateur de l'entreprise.
     */
    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Seul l\'administrateur de l\'entreprise peut gerer les factures.');
        }
    }

    // ─── PAGES ───────────────────────────────────────────────────────

    /**
     * Page de gestion des factures.
     */
    public function index()
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        return view('company', [
            'page' => 'company-invoices',
            'clientId' => $clientId,
        ]);
    }

    // ─── API: LISTE ──────────────────────────────────────────────────

    /**
     * API: Liste toutes les factures de l'entreprise.
     */
    public function listAll(Request $request)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $query = CompanyInvoice::forClient($clientId)
            ->with(['items', 'payments', 'createdBy:id,name'])
            ->orderBy('created_at', 'desc');

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate($request->per_page ?? 20);

        return response()->json($invoices);
    }

    /**
     * API: Retourne une facture avec ses relations.
     */
    public function show($id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::forClient($clientId)
            ->with(['items', 'payments', 'createdBy:id,name'])
            ->findOrFail($id);

        return response()->json($invoice);
    }

    // ─── API: CREATION ───────────────────────────────────────────────

    /**
     * API: Cree une facture avec ses lignes.
     * Genere un numero unique: FACT-{annee}-{compteur}.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'type' => 'required|in:devis,facture,avoir',
            'recipient_name' => 'required|string|max:255',
            'recipient_address' => 'nullable|string|max:1000',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'notes' => 'nullable|string|max:5000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $number = $this->generateInvoiceNumber($clientId, $validated['type']);

        return DB::transaction(function () use ($validated, $clientId, $number) {
            $totalHt = 0;
            $totalTva = 0;
            $totalTtc = 0;

            // Preparer les lignes
            $items = [];
            foreach ($validated['items'] as $item) {
                $taxRate = (float) ($item['tax_rate'] ?? 0);
                $qty = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];

                $lineHt = $qty * $unitPrice;
                $lineTax = $lineHt * ($taxRate / 100);
                $lineTtc = $lineHt + $lineTax;

                $items[] = new CompanyInvoiceItem([
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'total_ht' => $lineHt,
                    'total_ttc' => $lineTtc,
                ]);

                $totalHt += $lineHt;
                $totalTva += $lineTax;
                $totalTtc += $lineTtc;
            }

            $invoice = CompanyInvoice::create([
                'client_id' => $clientId,
                'number' => $number,
                'type' => $validated['type'],
                'status' => 'brouillon',
                'recipient_name' => $validated['recipient_name'],
                'recipient_address' => $validated['recipient_address'] ?? null,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'total_ht' => $totalHt,
                'total_tva' => $totalTva,
                'total_ttc' => $totalTtc,
                'paid_amount' => 0,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $invoice->items()->saveMany($items);
            $invoice->load(['items', 'createdBy:id,name']);

            return response()->json([
                'message' => 'Facture creee avec succes.',
                'invoice' => $invoice,
            ], 201);
        });
    }

    /**
     * API: Met a jour une facture (seulement si brouillon).
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::forClient($clientId)->findOrFail($id);

        if ($invoice->status !== 'brouillon') {
            return response()->json(['message' => 'Seules les factures en brouillon peuvent etre modifiees.'], 403);
        }

        $validated = $request->validate([
            'recipient_name' => 'sometimes|string|max:255',
            'recipient_address' => 'nullable|string|max:1000',
            'issue_date' => 'sometimes|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'notes' => 'nullable|string|max:5000',
            'items' => 'sometimes|array|min:1',
            'items.*.description' => 'required_with:items|string|max:500',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($validated, $invoice) {
            $data = collect($validated)->except('items')->toArray();

            if ($request->has('items')) {
                $totalHt = 0;
                $totalTva = 0;
                $totalTtc = 0;
                $newItems = [];

                foreach ($validated['items'] as $item) {
                    $taxRate = (float) ($item['tax_rate'] ?? 0);
                    $qty = (float) $item['quantity'];
                    $unitPrice = (float) $item['unit_price'];

                    $lineHt = $qty * $unitPrice;
                    $lineTax = $lineHt * ($taxRate / 100);
                    $lineTtc = $lineHt + $lineTax;

                    $newItems[] = new CompanyInvoiceItem([
                        'description' => $item['description'],
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'tax_rate' => $taxRate,
                        'total_ht' => $lineHt,
                        'total_ttc' => $lineTtc,
                    ]);

                    $totalHt += $lineHt;
                    $totalTva += $lineTax;
                    $totalTtc += $lineTtc;
                }

                $data['total_ht'] = $totalHt;
                $data['total_tva'] = $totalTva;
                $data['total_ttc'] = $totalTtc;

                // Remplacer les lignes
                $invoice->items()->delete();
                $invoice->items()->saveMany($newItems);
            }

            $invoice->update($data);
            $invoice->load(['items', 'payments', 'createdBy:id,name']);

            return response()->json([
                'message' => 'Facture mise a jour.',
                'invoice' => $invoice,
            ]);
        });
    }

    /**
     * API: Supprime une facture (soft delete, seulement si brouillon).
     */
    public function destroy($id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::forClient($clientId)->findOrFail($id);

        if ($invoice->status !== 'brouillon') {
            return response()->json(['message' => 'Seules les factures en brouillon peuvent etre supprimees.'], 403);
        }

        $invoice->delete();

        return response()->json(['message' => 'Facture supprimee.']);
    }

    // ─── API: STATUT ─────────────────────────────────────────────────

    /**
     * API: Met a jour le statut d'une facture.
     * brouillon -> emise -> payee
     * brouillon -> annulee
     * emise -> impayee
     */
    public function updateStatus(Request $request, $id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::forClient($clientId)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:brouillon,emise,payee,annulee,impayee',
        ]);

        $newStatus = $validated['status'];

        // Regles de transition
        $allowed = $this->getAllowedTransitions($invoice->status);

        if (!in_array($newStatus, $allowed)) {
            return response()->json([
                'message' => "Transition de statut non autorisee: {$invoice->status} -> {$newStatus}.",
            ], 403);
        }

        $invoice->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Statut mis a jour.',
            'invoice' => $invoice->fresh()->load(['items', 'payments', 'createdBy:id,name']),
        ]);
    }

    /**
     * Transitions autorisees par statut.
     */
    private function getAllowedTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            'brouillon' => ['emise', 'annulee'],
            'emise' => ['payee', 'impayee', 'annulee'],
            'impayee' => ['payee', 'annulee'],
            'payee' => [],       // Une facture payee ne peut pas changer
            'annulee' => [],     // Une facture annulee ne peut pas changer
            default => [],
        };
    }

    // ─── API: PAIEMENTS ──────────────────────────────────────────────

    /**
     * API: Enregistre un paiement sur une facture.
     */
    public function storePayment(Request $request, $id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::forClient($clientId)->findOrFail($id);

        if ($invoice->status === 'annulee') {
            return response()->json(['message' => 'Impossible d\'ajouter un paiement sur une facture annulee.'], 403);
        }

        if ($invoice->is_fully_paid) {
            return response()->json(['message' => 'Cette facture est deja entierement payee.'], 403);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => "required|numeric|min:0.01|max:{$invoice->balance}",
            'method' => 'required|in:cash,transfer,momo,cheque',
            'reference' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $invoice) {
            $payment = CompanyPayment::create([
                'invoice_id' => $invoice->id,
                'date' => $validated['date'],
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'reference' => $validated['reference'] ?? null,
            ]);

            // Mettre a jour le montant paye
            $newPaid = (float) $invoice->paid_amount + (float) $validated['amount'];
            $invoice->update(['paid_amount' => $newPaid]);

            // Si le solde est nul, marquer comme payee
            $balance = (float) $invoice->total_ttc - $newPaid;
            if ($balance <= 0 && $invoice->status !== 'payee') {
                $invoice->update(['status' => 'payee']);
            }

            $invoice->load(['items', 'payments', 'createdBy:id,name']);

            return response()->json([
                'message' => 'Paiement enregistre.',
                'payment' => $payment->fresh(),
                'invoice' => $invoice,
            ], 201);
        });
    }

    // ─── API: STATISTIQUES ───────────────────────────────────────────

    /**
     * API: Statistiques des factures pour l'entreprise.
     */
    public function stats()
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $invoices = CompanyInvoice::forClient($clientId);

        $stats = [
            'total_count' => (clone $invoices)->count(),
            'total_ht' => (float) (clone $invoices)->sum('total_ht'),
            'total_ttc' => (float) (clone $invoices)->sum('total_ttc'),
            'total_paid' => (float) (clone $invoices)->sum('paid_amount'),
            'total_due' => (float) (clone $invoices)->get()->sum(fn($i) => $i->balance),
            'by_status' => (clone $invoices)
                ->select('status', DB::raw('count(*) as count'), DB::raw('sum(total_ttc) as total'))
                ->groupBy('status')
                ->get(),
            'by_type' => (clone $invoices)
                ->select('type', DB::raw('count(*) as count'), DB::raw('sum(total_ttc) as total'))
                ->groupBy('type')
                ->get(),
            'monthly' => (clone $invoices)
                ->select(DB::raw("strftime('%Y-%m', issue_date) as month"), DB::raw('count(*) as count'), DB::raw('sum(total_ttc) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
            'recent' => (clone $invoices)
                ->with(['items', 'createdBy:id,name'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    // ─── PRIVE ───────────────────────────────────────────────────────

    /**
     * Genere un numero de facture unique.
     * Format: FACT-{annee}-{compteur}
     */
    private function generateInvoiceNumber(int $clientId, string $type): string
    {
        $year = date('Y');
        $prefix = match ($type) {
            'devis' => 'DEV',
            'avoir' => 'AVOIR',
            default => 'FACT',
        };

        $lastInvoice = CompanyInvoice::forClient($clientId)
            ->where('number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->number);
            $counter = (int) end($parts) + 1;
        } else {
            $counter = 1;
        }

        return "{$prefix}-{$year}-" . str_pad($counter, 4, '0', STR_PAD_LEFT);
    }
}
