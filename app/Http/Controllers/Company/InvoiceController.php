<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyInvoice;
use App\Models\CompanyInvoiceItem;
use App\Models\CompanyPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends BaseCompanyController
{
    /**
     * Affiche la page de facturation.
     */
    public function index()
    {
        $clientId = $this->getClientId();
        return view('company', ['page' => 'company-invoices', 'clientId' => $clientId]);
    }

    /**
     * API: Liste toutes les factures de l'entreprise, filtrées par client_id.
     */
    public function listAll(Request $request)
    {
        $clientId = $this->getClientId();

        $query = CompanyInvoice::byClient($clientId)
            ->withCount('items', 'payments')
            ->latest();

        // Filtre optionnel par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->get()->map(function ($inv) {
            return [
                'id'             => $inv->id,
                'number'         => $inv->number,
                'type'           => $inv->type,
                'status'         => $inv->status,
                'computed_status' => $inv->computed_status,
                'recipient_name' => $inv->recipient_name,
                'issue_date'     => $inv->issue_date?->format('Y-m-d'),
                'due_date'       => $inv->due_date?->format('Y-m-d'),
                'total_ht'       => (float) $inv->total_ht,
                'total_tva'      => (float) $inv->total_tva,
                'total_ttc'      => (float) $inv->total_ttc,
                'paid_amount'    => (float) $inv->paid_amount,
                'items_count'    => $inv->items_count,
                'payments_count' => $inv->payments_count,
                'created_at'     => $inv->created_at?->format('d/m/Y'),
            ];
        });

        return response()->json($invoices);
    }

    /**
     * API: Crée une facture avec ses lignes.
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'type'             => 'required|in:invoice,credit_note,devis',
            'recipient_name'   => 'required|string|max:255',
            'recipient_address'=> 'nullable|string',
            'issue_date'       => 'required|date',
            'due_date'         => 'required|date|after_or_equal:issue_date',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.tax_rate'    => 'required|numeric|min:0|max:100',
        ]);

        $user = Auth::user();

        $invoice = DB::transaction(function () use ($validated, $clientId, $user) {
            // Génération du numéro auto: FACT-{year}-{counter}
            $year = now()->format('Y');
            $lastInvoice = CompanyInvoice::byClient($clientId)
                ->where('number', 'like', "FACT-{$year}-%")
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($lastInvoice) {
                $parts = explode('-', $lastInvoice->number);
                $counter = (int) end($parts) + 1;
            } else {
                $counter = 1;
            }

            $number = "FACT-{$year}-" . str_pad($counter, 4, '0', STR_PAD_LEFT);

            // Calcul des totaux
            $totalHt  = 0;
            $totalTva = 0;

            $itemsData = [];
            foreach ($validated['items'] as $item) {
                $lineHt  = $item['quantity'] * $item['unit_price'];
                $lineTva = $lineHt * ($item['tax_rate'] / 100);
                $lineTtc = $lineHt + $lineTva;

                $totalHt  += $lineHt;
                $totalTva += $lineTva;

                $itemsData[] = new CompanyInvoiceItem([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'tax_rate'    => $item['tax_rate'],
                    'total_ht'    => $lineHt,
                    'total_ttc'   => $lineTtc,
                ]);
            }

            $totalTtc = $totalHt + $totalTva;

            $invoice = CompanyInvoice::create([
                'client_id'        => $clientId,
                'number'           => $number,
                'type'             => $validated['type'],
                'status'           => 'draft',
                'recipient_name'   => $validated['recipient_name'],
                'recipient_address'=> $validated['recipient_address'] ?? null,
                'issue_date'       => $validated['issue_date'],
                'due_date'         => $validated['due_date'],
                'total_ht'         => $totalHt,
                'total_tva'        => $totalTva,
                'total_ttc'        => $totalTtc,
                'paid_amount'      => 0,
                'notes'            => $validated['notes'] ?? null,
                'created_by'       => $user->id,
            ]);

            $invoice->items()->saveMany($itemsData);

            return $invoice->fresh(['items']);
        });

        return response()->json([
            'message' => 'Facture créée avec succès.',
            'invoice' => $invoice,
        ], 201);
    }

    /**
     * API: Affiche une facture avec ses lignes et ses paiements.
     */
    public function show($id)
    {
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::byClient($clientId)
            ->with(['items', 'payments', 'createdBy'])
            ->findOrFail($id);

        return response()->json([
            'invoice' => [
                'id'             => $invoice->id,
                'client_id'      => $invoice->client_id,
                'number'         => $invoice->number,
                'type'           => $invoice->type,
                'status'         => $invoice->status,
                'computed_status' => $invoice->computed_status,
                'recipient_name' => $invoice->recipient_name,
                'recipient_address' => $invoice->recipient_address,
                'issue_date'     => $invoice->issue_date?->format('Y-m-d'),
                'due_date'       => $invoice->due_date?->format('Y-m-d'),
                'total_ht'       => (float) $invoice->total_ht,
                'total_tva'      => (float) $invoice->total_tva,
                'total_ttc'      => (float) $invoice->total_ttc,
                'paid_amount'    => (float) $invoice->paid_amount,
                'notes'          => $invoice->notes,
                'created_by_name'=> $invoice->createdBy?->name,
                'items'          => $invoice->items->map(function ($item) {
                    return [
                        'id'          => $item->id,
                        'description' => $item->description,
                        'quantity'    => (float) $item->quantity,
                        'unit_price'  => (float) $item->unit_price,
                        'tax_rate'    => (float) $item->tax_rate,
                        'total_ht'    => (float) $item->total_ht,
                        'total_ttc'   => (float) $item->total_ttc,
                    ];
                }),
                'payments'       => $invoice->payments->map(function ($payment) {
                    return [
                        'id'        => $payment->id,
                        'date'      => $payment->date?->format('Y-m-d'),
                        'amount'    => (float) $payment->amount,
                        'method'    => $payment->method,
                        'reference' => $payment->reference,
                    ];
                }),
                'created_at'     => $invoice->created_at?->format('d/m/Y'),
            ],
        ]);
    }

    /**
     * API: Met à jour une facture en brouillon.
     */
    public function update(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::byClient($clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $validated = $request->validate([
            'recipient_name'   => 'sometimes|string|max:255',
            'recipient_address'=> 'sometimes|nullable|string',
            'issue_date'       => 'sometimes|date',
            'due_date'         => 'sometimes|date|after_or_equal:issue_date',
            'notes'            => 'sometimes|nullable|string',
            'items'            => 'sometimes|array|min:1',
            'items.*.description' => 'required_with:items|string',
            'items.*.quantity'    => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price'  => 'required_with:items|numeric|min:0',
            'items.*.tax_rate'    => 'required_with:items|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            $updateData = collect($validated)->except('items')->toArray();

            // Si des lignes sont fournies, recalculer les totaux
            if ($request->has('items')) {
                $totalHt  = 0;
                $totalTva = 0;

                $itemsData = [];
                foreach ($validated['items'] as $item) {
                    $lineHt  = $item['quantity'] * $item['unit_price'];
                    $lineTva = $lineHt * ($item['tax_rate'] / 100);
                    $lineTtc = $lineHt + $lineTva;

                    $totalHt  += $lineHt;
                    $totalTva += $lineTva;

                    $itemsData[] = new CompanyInvoiceItem([
                        'description' => $item['description'],
                        'quantity'    => $item['quantity'],
                        'unit_price'  => $item['unit_price'],
                        'tax_rate'    => $item['tax_rate'],
                        'total_ht'    => $lineHt,
                        'total_ttc'   => $lineTtc,
                    ]);
                }

                $updateData['total_ht']  = $totalHt;
                $updateData['total_tva'] = $totalTva;
                $updateData['total_ttc'] = $totalHt + $totalTva;

                // Remplacer les lignes
                $invoice->items()->delete();
                $invoice->items()->saveMany($itemsData);
            }

            $invoice->update($updateData);
        });

        return response()->json([
            'message' => 'Facture mise à jour.',
            'invoice' => $invoice->fresh(['items']),
        ]);
    }

    /**
     * API: Supprime une facture en brouillon.
     */
    public function destroy($id)
    {
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::byClient($clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $invoice->delete();

        return response()->json(['message' => 'Facture supprimée.']);
    }

    /**
     * API: Change le statut d'une facture.
     */
    public function updateStatus(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::byClient($clientId)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:draft,sent,paid,cancelled,overdue',
        ]);

        $invoice->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Statut mis à jour.',
            'status'  => $invoice->fresh()->status,
        ]);
    }

    /**
     * API: Enregistre un paiement et met à jour le montant payé.
     */
    public function storePayment(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::byClient($clientId)->findOrFail($id);

        $validated = $request->validate([
            'date'      => 'required|date',
            'amount'    => 'required|numeric|min:0.01',
            'method'    => 'required|in:cash,transfer,momo,cheque',
            'reference' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            CompanyPayment::create([
                'invoice_id' => $invoice->id,
                'date'       => $validated['date'],
                'amount'     => $validated['amount'],
                'method'     => $validated['method'],
                'reference'  => $validated['reference'] ?? null,
            ]);

            $invoice->increment('paid_amount', $validated['amount']);

            // Si total_ttc atteint, passer en paid
            if ((float) $invoice->fresh()->paid_amount >= (float) $invoice->total_ttc) {
                $invoice->update(['status' => 'paid']);
            }
        });

        return response()->json([
            'message' => 'Paiement enregistré.',
            'invoice' => $invoice->fresh(['payments']),
        ]);
    }

    /**
     * API: Statistiques agrégées des factures.
     */
    public function stats()
    {
        $clientId = $this->getClientId();

        $invoices = CompanyInvoice::byClient($clientId)->get();

        $stats = [
            'total_invoices' => $invoices->count(),
            'total_ht'       => (float) $invoices->sum('total_ht'),
            'total_tva'      => (float) $invoices->sum('total_tva'),
            'total_ttc'      => (float) $invoices->sum('total_ttc'),
            'total_paid'     => (float) $invoices->sum('paid_amount'),
            'total_due'      => (float) $invoices->sum('total_ttc') - (float) $invoices->sum('paid_amount'),
            'by_status'      => $invoices->groupBy('status')->map(function ($group) {
                return [
                    'count'      => $group->count(),
                    'total_ttc'  => (float) $group->sum('total_ttc'),
                ];
            }),
        ];

        return response()->json($stats);
    }
}
