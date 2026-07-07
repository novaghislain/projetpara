<?php

namespace App\Http\Controllers\Company\Compta;

use App\Http\Controllers\Controller;
use App\Models\CompanyInvoice;
use App\Models\CompanyInvoiceItem;
use App\Models\CompanyPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class FactureController extends Controller
{
    protected function getClientId(): int
    {
        $user = Auth::user();
        return (int) ($user->active_client_id ?? $user->client_id);
    }

    /**
     * Affiche la page de facturation (SPA).
     */
    public function index()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->route('select.context')
                ->withErrors(['Aucune entreprise associée.']);
        }
        return view('company', [
            'page' => 'compta-factures',
            'clientId' => $clientId,
        ]);
    }

    /**
     * Détail d'une facture avec lignes et paiements.
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
                'items'          => $invoice->items->map(fn($item) => [
                    'id'          => $item->id,
                    'description' => $item->description,
                    'quantity'    => (float) $item->quantity,
                    'unit_price'  => (float) $item->unit_price,
                    'tax_rate'    => (float) $item->tax_rate,
                    'total_ht'    => (float) $item->total_ht,
                    'total_ttc'   => (float) $item->total_ttc,
                ]),
                'payments'       => $invoice->payments->map(fn($p) => [
                    'id'        => $p->id,
                    'date'      => $p->date?->format('Y-m-d'),
                    'amount'    => (float) $p->amount,
                    'method'    => $p->method,
                    'reference' => $p->reference,
                ]),
                'created_at'     => $invoice->created_at?->format('d/m/Y'),
            ],
        ]);
    }

    /**
     * Crée une facture avec ses lignes.
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'type'              => 'required|in:invoice,credit_note,devis',
            'recipient_name'    => 'required|string|max:255',
            'recipient_address' => 'nullable|string',
            'issue_date'        => 'required|date',
            'due_date'          => 'required|date|after_or_equal:issue_date',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.tax_rate'    => 'required|numeric|min:0|max:100',
        ]);

        $user = Auth::user();

        $invoice = DB::transaction(function () use ($validated, $clientId, $user) {
            $year = now()->format('Y');
            $prefix = match ($validated['type']) {
                'credit_note' => 'AVOIR',
                'devis'       => 'DEVIS',
                default       => 'FACT',
            };

            $lastInvoice = CompanyInvoice::byClient($clientId)
                ->where('number', 'like', "{$prefix}-{$year}-%")
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $counter = $lastInvoice
                ? (int) explode('-', $lastInvoice->number)[2] + 1
                : 1;

            $number = "{$prefix}-{$year}-" . str_pad($counter, 4, '0', STR_PAD_LEFT);

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
     * Met à jour une facture en brouillon.
     */
    public function update(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::byClient($clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $validated = $request->validate([
            'recipient_name'    => 'sometimes|string|max:255',
            'recipient_address' => 'sometimes|nullable|string',
            'issue_date'        => 'sometimes|date',
            'due_date'          => 'sometimes|date|after_or_equal:issue_date',
            'notes'             => 'sometimes|nullable|string',
            'items'             => 'sometimes|array|min:1',
            'items.*.description' => 'required_with:items|string',
            'items.*.quantity'    => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price'  => 'required_with:items|numeric|min:0',
            'items.*.tax_rate'    => 'required_with:items|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($invoice, $validated, $request) {
            $updateData = collect($validated)->except('items')->toArray();

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
     * Supprime une facture en brouillon.
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
     * Envoie la facture par email avec PDF en pièce jointe.
     */
    public function send($id)
    {
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::byClient($clientId)
            ->with(['items', 'payments'])
            ->findOrFail($id);

        if (!$invoice->recipient_name) {
            return response()->json(['message' => 'Destinataire manquant.'], 400);
        }

        // Générer le PDF
        $pdf = Pdf::loadView('pdfs.company_invoice', [
            'invoice' => $invoice,
            'company' => Auth::user()->activeClient,
        ]);

        try {
            Mail::send([], [], function ($message) use ($invoice, $pdf) {
                $recipientEmail = $invoice->recipient_email ?? Auth::user()->email;
                $message->to($recipientEmail)
                    ->subject('Facture ' . $invoice->number)
                    ->html(view('emails.invoice-send', [
                        'invoice' => $invoice,
                        'recipientName' => $invoice->recipient_name,
                    ])->render())
                    ->attachData($pdf->output(), "facture-{$invoice->number}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });

            $invoice->update(['status' => 'sent']);

            return response()->json(['message' => 'Facture envoyée par email.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur d\'envoi : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Enregistre un paiement et met à jour le statut.
     */
    public function pay(Request $request, $id)
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
     * Génère et télécharge le PDF de la facture.
     */
    public function pdf($id)
    {
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::byClient($clientId)
            ->with(['items', 'payments', 'createdBy'])
            ->findOrFail($id);

        $company = Auth::user()->activeClient;

        $pdf = Pdf::loadView('pdfs.company_invoice', [
            'invoice' => $invoice,
            'company' => $company,
        ]);

        return $pdf->download("facture-{$invoice->number}.pdf");
    }

    /**
     * Annule une facture et génère un avoir (credit note).
     */
    public function cancel(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $invoice = CompanyInvoice::byClient($clientId)->findOrFail($id);

        if ($invoice->status === 'cancelled') {
            return response()->json(['message' => 'Facture déjà annulée.'], 400);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($invoice, $clientId, $validated) {
            // Marquer la facture comme annulée
            $invoice->update(['status' => 'cancelled']);

            // Générer un avoir (credit note) reprenant les mêmes lignes en négatif
            $year = now()->format('Y');
            $lastCredit = CompanyInvoice::byClient($clientId)
                ->where('number', 'like', "AVOIR-{$year}-%")
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $counter = $lastCredit
                ? (int) explode('-', $lastCredit->number)[2] + 1
                : 1;

            $number = "AVOIR-{$year}-" . str_pad($counter, 4, '0', STR_PAD_LEFT);

            $creditNote = CompanyInvoice::create([
                'client_id'        => $clientId,
                'number'           => $number,
                'type'             => 'credit_note',
                'status'           => 'sent',
                'recipient_name'   => $invoice->recipient_name,
                'recipient_address'=> $invoice->recipient_address,
                'issue_date'       => now(),
                'due_date'         => now(),
                'total_ht'         => -$invoice->total_ht,
                'total_tva'        => -$invoice->total_tva,
                'total_ttc'        => -$invoice->total_ttc,
                'paid_amount'      => 0,
                'notes'            => $validated['reason']
                    ?? "Avoir pour annulation de la facture {$invoice->number}",
                'created_by'       => Auth::id(),
            ]);

            // Copier les lignes de la facture en négatif
            foreach ($invoice->items as $item) {
                $creditNote->items()->create([
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'tax_rate'    => $item->tax_rate,
                    'total_ht'    => -$item->total_ht,
                    'total_ttc'   => -$item->total_ttc,
                ]);
            }
        });

        return response()->json([
            'message' => 'Facture annulée. Un avoir a été généré.',
        ]);
    }
}
