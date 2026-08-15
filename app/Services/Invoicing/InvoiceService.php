<?php

namespace App\Services\Invoicing;

use App\Models\AccountingAccount;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoiceSequence;
use App\Models\Journal;
use App\Models\Partner;
use App\Models\Payment;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    private JournalEntryService $entryService;
    private \App\Services\TaxEngineService $taxEngineService;

    public function __construct(JournalEntryService $entryService, \App\Services\TaxEngineService $taxEngineService)
    {
        $this->entryService = $entryService;
        $this->taxEngineService = $taxEngineService;
    }

    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Crée une facture avec ses lignes.
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $clientId = $this->getClientId();

            $partner = Partner::where('id', $data['partner_id'])
                ->where('client_id', $clientId)
                ->firstOrFail();

            // Déterminer le préfixe
            $prefix = match ($data['type']) {
                'customer_invoice' => 'FAC',
                'supplier_invoice' => 'ACH',
                'credit_note' => 'AVOIR',
                'debit_note' => 'ND',
                default => 'FAC',
            };

            // Générer le numéro
            $invoiceNumber = InvoiceSequence::getNextNumber($clientId, $prefix);

            // Calculer les totaux
            $subtotal = 0;
            $discount = 0;
            $vatTotal = 0;
            $total = 0;

            $linesData = [];
            foreach ($data['lines'] as $index => $line) {
                $qty = (float) ($line['quantity'] ?? 1);
                $unitPrice = (float) ($line['unit_price'] ?? 0);
                $lineDiscount = (float) ($line['discount_percent'] ?? 0);
                $vatRate = (float) ($line['vat_rate'] ?? 0);

                $lineSubtotal = $qty * $unitPrice;
                $lineDiscountAmount = $lineSubtotal * ($lineDiscount / 100);
                $netUnitPrice = $unitPrice * (1 - $lineDiscount / 100);
                $lineNetTotal = $lineSubtotal - $lineDiscountAmount;
                $lineVat = $lineNetTotal * ($vatRate / 100);
                $lineTotal = $lineNetTotal + $lineVat;

                $subtotal += $lineSubtotal;
                $discount += $lineDiscountAmount;
                $vatTotal += $lineVat;
                $total += $lineTotal;

                // Compte de vente/achat par défaut (SYSCOHADA)
                $accountCode = ($data['type'] === 'customer_invoice') ? '701' : '601';
                $accountId = AccountingAccount::where('client_id', $clientId)
                    ->where('code', $accountCode)
                    ->value('id')
                    ?? AccountingAccount::where('client_id', 0)
                        ->where('code', $accountCode)
                        ->value('id');

                // Compte TVA
                $vatAccountId = null;
                if ($vatRate > 0) {
                    $vatCode = ($data['type'] === 'customer_invoice') ? '443' : '445';
                    $vatAccountId = AccountingAccount::where('client_id', $clientId)
                        ->where('code', $vatCode)
                        ->value('id')
                        ?? AccountingAccount::where('client_id', 0)
                            ->where('code', $vatCode)
                            ->value('id');
                }

                $linesData[] = [
                    'client_id' => $clientId,
                    'line_number' => $index + 1,
                    'description' => $line['description'],
                    'product_code' => $line['product_code'] ?? null,
                    'quantity' => $qty,
                    'unit' => $line['unit'] ?? 'pce',
                    'unit_price' => $unitPrice,
                    'discount' => $lineDiscountAmount,
                    'discount_percent' => $lineDiscount,
                    'net_unit_price' => $netUnitPrice,
                    'subtotal' => $lineNetTotal,
                    'vat_code' => $line['vat_code'] ?? null,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $lineVat,
                    'total' => $lineTotal,
                    'account_id' => $accountId,
                    'vat_account_id' => $vatAccountId,
                ];
            }

            // Remise globale
            $globalDiscountPercent = (float) ($data['discount_percent'] ?? 0);
            $globalDiscount = $total * ($globalDiscountPercent / 100);
            $discount += $globalDiscount;
            $total -= $globalDiscount;

            // --- MOTEUR FISCAL AIB ---
            // On vérifie si l'entreprise a un IFU valide
            $hasIfu = !empty($partner->tax_id);
            // Par défaut, on applique l'AIB pour les factures fournisseurs (retenue à la source)
            $applyAib = ($data['type'] === 'supplier_invoice');
            
            $aibAmount = 0;
            if ($applyAib) {
                $taxBase = $subtotal - $discount;
                $aibAmount = $this->taxEngineService->calculateAIB($taxBase, $hasIfu);
                // Le total à payer diminue car on retient l'AIB à la source
                $total -= $aibAmount;
            }

            // Créer la facture
            $invoice = Invoice::create([
                'client_id' => $clientId,
                'type' => $data['type'],
                'invoice_number' => $invoiceNumber,
                'partner_id' => $partner->id,
                'partner_name' => $partner->full_name,
                'partner_tax_id' => $partner->tax_id,
                'partner_address' => $partner->address,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'delivery_date' => $data['delivery_date'] ?? null,
                'payment_term' => $data['payment_term'] ?? ($partner->payment_term_days . ' jours'),
                'payment_method' => $data['payment_method'] ?? $partner->payment_method,
                'currency' => $data['currency'] ?? $partner->currency ?? 'XOF',
                'exchange_rate' => 1,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_percent' => $globalDiscountPercent,
                'tax_base' => $subtotal - $discount,
                'vat_total' => $vatTotal,
                'aib_amount' => $aibAmount,
                'total' => $total,
                'paid_amount' => 0,
                'balance_due' => $total,
                'status' => 'draft',
                'related_invoice_id' => $data['related_invoice_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Créer les lignes
            foreach ($linesData as $lineData) {
                $lineData['invoice_id'] = $invoice->id;
                InvoiceLine::create($lineData);
            }

            return $invoice->fresh(['lines', 'partner']);
        });
    }

    /**
     * Valide une facture et génère l'écriture comptable.
     */
    public function validateInvoice(string $invoiceId): Invoice
    {
        return DB::transaction(function () use ($invoiceId) {
            $invoice = Invoice::with(['lines', 'partner'])
                ->where('id', $invoiceId)
                ->where('client_id', $this->getClientId())
                ->firstOrFail();

            if ($invoice->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Seules les factures en brouillon peuvent être validées.',
                ]);
            }

            $clientId = $this->getClientId();

            // Journal selon le type
            $journalCode = $invoice->type === 'customer_invoice' ? 'VE' : 'AC';
            $journal = Journal::where('client_id', $clientId)
                ->where('code', $journalCode)
                ->firstOrFail();

            // Compte client (411) ou fournisseur (401)
            $partnerAccountCode = $invoice->type === 'customer_invoice' ? '411' : '401';
            $partnerAccount = AccountingAccount::where('client_id', $clientId)
                ->where('code', $partnerAccountCode)
                ->firstOrFail();

            // Construire les lignes d'écriture comptable
            $lines = [];

            // Ligne client/fournisseur
            $lines[] = [
                'account_id' => $partnerAccount->id,
                'description' => $invoice->type === 'customer_invoice'
                    ? "Facture client {$invoice->invoice_number}"
                    : "Facture fournisseur {$invoice->invoice_number}",
                'debit' => $invoice->type === 'customer_invoice' ? (float) $invoice->total : 0,
                'credit' => $invoice->type === 'supplier_invoice' ? (float) $invoice->total : 0,
                'partner_id' => $invoice->partner_id,
                'partner_type' => $invoice->type === 'customer_invoice' ? 'customer' : 'supplier',
            ];

            // Calculer le ratio net pour répartir la remise globale proportionnellement
            $subtotalTotal = (float) $invoice->subtotal;
            $netRevenue = (float) $invoice->total - (float) $invoice->vat_total;
            $revenueFactor = ($subtotalTotal > 0) ? $netRevenue / $subtotalTotal : 1;

            // Lignes de vente/achat et TVA
            foreach ($invoice->lines as $line) {
                $lineSubtotal = (float) $line->subtotal;
                $lineVat = (float) $line->vat_amount;

                // Appliquer le ratio net pour intégrer la remise globale
                $netLineAmount = $lineSubtotal * $revenueFactor;

                if ($netLineAmount > 0) {
                    $lines[] = [
                        'account_id' => $line->account_id,
                        'description' => $line->description,
                        'debit' => $invoice->type === 'customer_invoice' ? 0 : $netLineAmount,
                        'credit' => $invoice->type === 'customer_invoice' ? $netLineAmount : 0,
                    ];
                }

                if ($lineVat > 0 && $line->vat_account_id) {
                    $lines[] = [
                        'account_id' => $line->vat_account_id,
                        'description' => "TVA sur {$line->description}",
                        'debit' => $invoice->type === 'customer_invoice' ? 0 : $lineVat,
                        'credit' => $invoice->type === 'customer_invoice' ? $lineVat : 0,
                    ];
                }
            }

            // Créer l'écriture comptable
            $entryData = [
                'journal_id' => $journal->id,
                'entry_date' => $invoice->invoice_date->format('Y-m-d'),
                'value_date' => $invoice->invoice_date->format('Y-m-d'),
                'reference' => $invoice->invoice_number,
                'description' => "{$invoice->partner_name} - {$invoice->invoice_number}",
                'lines' => $lines,
            ];

            $entry = $this->entryService->createEntry($entryData);

            // Poster l'écriture
            $this->entryService->postEntry($entry->id);

            // Mettre à jour la facture
            $invoice->update([
                'status' => 'sent',
                'journal_entry_id' => $entry->id,
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);

            return $invoice->fresh(['lines', 'partner', 'journalEntry']);
        });
    }

    /**
     * Enregistre un paiement sur une facture.
     */
    public function recordPayment(string $invoiceId, array $paymentData): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $paymentData) {
            $clientId = $this->getClientId();

            $invoice = Invoice::where('id', $invoiceId)
                ->where('client_id', $clientId)
                ->firstOrFail();

            $amount = (float) $paymentData['amount'];

            if ($amount > (float) $invoice->balance_due) {
                throw ValidationException::withMessages([
                    'amount' => 'Le montant du paiement (' . $amount
                        . ') dépasse le solde dû (' . $invoice->balance_due . ').',
                ]);
            }

            // Créer le paiement
            $payment = Payment::create([
                'client_id' => $clientId,
                'type' => $invoice->type === 'customer_invoice' ? 'incoming' : 'outgoing',
                'payment_number' => InvoiceSequence::getNextNumber($clientId, 'REG'),
                'partner_id' => $invoice->partner_id,
                'invoice_id' => $invoice->id,
                'payment_date' => $paymentData['payment_date'],
                'amount' => $amount,
                'payment_method' => $paymentData['payment_method'] ?? 'bank_transfer',
                'reference' => $paymentData['reference'] ?? null,
                'bank_account_id' => $paymentData['bank_account_id'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'status' => 'completed',
            ]);

            // Mettre à jour le solde
            $newPaidAmount = (float) $invoice->paid_amount + $amount;
            $newBalanceDue = (float) $invoice->total - $newPaidAmount;

            $newStatus = match (true) {
                $newBalanceDue <= 0 => 'paid',
                $newPaidAmount > 0 => 'partially_paid',
                default => $invoice->status,
            };

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'balance_due' => $newBalanceDue,
                'status' => $newStatus,
            ]);

            // Écriture comptable du paiement
            $bankAccountCode = '521'; // Banque SYSCOHADA
            $bankAccountId = $paymentData['bank_account_id']
                ?? AccountingAccount::where('client_id', $clientId)
                    ->where('code', $bankAccountCode)
                    ->value('id')
                ?? AccountingAccount::where('client_id', 0)
                    ->where('code', $bankAccountCode)
                    ->value('id')
                ?? AccountingAccount::where('client_id', $clientId)
                    ->where('code', '512')
                    ->value('id'); // Fallback 512 si 521 inexistant

            $partnerAccountCode = $invoice->type === 'customer_invoice' ? '411' : '401';
            $partnerAccount = AccountingAccount::where('client_id', $clientId)
                ->where('code', $partnerAccountCode)
                ->firstOrFail();

            $journal = Journal::where('client_id', $clientId)
                ->where('code', 'BQ')
                ->firstOrFail();

            $entryData = [
                'journal_id' => $journal->id,
                'entry_date' => $paymentData['payment_date'],
                'reference' => $payment->payment_number,
                'description' => "Paiement {$payment->payment_number} - {$invoice->invoice_number}",
                'lines' => [
                    [
                        'account_id' => $bankAccountId,
                        'description' => "Règlement {$invoice->invoice_number}",
                        'debit' => $invoice->type === 'customer_invoice' ? $amount : 0,
                        'credit' => $invoice->type === 'supplier_invoice' ? $amount : 0,
                    ],
                    [
                        'account_id' => $partnerAccount->id,
                        'description' => "Règlement {$invoice->invoice_number}",
                        'debit' => $invoice->type === 'customer_invoice' ? 0 : $amount,
                        'credit' => $invoice->type === 'supplier_invoice' ? 0 : $amount,
                        'partner_id' => $invoice->partner_id,
                        'partner_type' => $invoice->type === 'customer_invoice' ? 'customer' : 'supplier',
                    ],
                ],
            ];

            $entry = $this->entryService->createEntry($entryData);
            $this->entryService->postEntry($entry->id);

            $payment->update(['journal_entry_id' => $entry->id]);

            return $invoice->fresh(['lines', 'partner', 'payments']);
        });
    }

    /**
     * Annule une facture.
     */
    public function cancelInvoice(string $invoiceId, ?string $reason = null): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $reason) {
            $invoice = Invoice::where('id', $invoiceId)
                ->where('client_id', $this->getClientId())
                ->firstOrFail();

            if (in_array($invoice->status, ['cancelled', 'paid'])) {
                throw ValidationException::withMessages([
                    'status' => 'Impossible d\'annuler une facture ' . $invoice->status,
                ]);
            }

            $invoice->update([
                'status' => 'cancelled',
                'notes' => ($invoice->notes ?? '')
                    . "\n[ANNULÉ: " . ($reason ?? 'Sans motif') . ']',
            ]);

            return $invoice->fresh();
        });
    }
}
