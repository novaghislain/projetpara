<?php

namespace App\Services\Tax;

use App\Models\VatDeclaration;
use App\Models\VatDeclarationLine;
use App\Models\VatDeclarationInvoice;
use App\Models\Invoice;
use App\Models\VatRate;
use App\Models\Journal;
use App\Models\AccountingAccount;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class VatDeclarationService
{
    private JournalEntryService $entryService;

    public function __construct(JournalEntryService $entryService)
    {
        $this->entryService = $entryService;
    }

    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Calcule les données pour une déclaration de TVA
     */
    public function computeDeclaration(int $clientId, string $periodType, int $year, ?int $month = null, ?int $quarter = null): array
    {
        [$startDate, $endDate, $dueDate] = $this->getPeriodDates($periodType, $year, $month, $quarter);

        // Factures collectées (ventes) sur la période
        $collectedInvoices = Invoice::where('client_id', $clientId)
            ->where('type', 'customer_invoice')
            ->whereIn('status', ['sent', 'confirmed', 'partially_paid', 'paid'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->with('lines')
            ->get();

        // Factures déductibles (achats) sur la période
        $deductibleInvoices = Invoice::where('client_id', $clientId)
            ->where('type', 'supplier_invoice')
            ->whereIn('status', ['sent', 'confirmed', 'partially_paid', 'paid'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->with('lines')
            ->get();

        // Calculer par taux
        $collectedByRate = [];
        $deductibleByRate = [];

        foreach ($collectedInvoices as $invoice) {
            foreach ($invoice->lines as $line) {
                $vatRate = (float) ($line->vat_rate ?? 0);
                if ($vatRate > 0) {
                    $key = $this->getRateCode($vatRate);

                    if (!isset($collectedByRate[$key])) {
                        $collectedByRate[$key] = ['base' => 0, 'vat' => 0, 'count' => 0, 'rate' => $vatRate];
                    }
                    $collectedByRate[$key]['base'] += (float) ($line->subtotal ?? 0);
                    $collectedByRate[$key]['vat'] += (float) ($line->vat_amount ?? 0);
                    $collectedByRate[$key]['count']++;
                }
            }
        }

        foreach ($deductibleInvoices as $invoice) {
            foreach ($invoice->lines as $line) {
                $vatRate = (float) ($line->vat_rate ?? 0);
                if ($vatRate > 0) {
                    $key = $this->getRateCode($vatRate);

                    if (!isset($deductibleByRate[$key])) {
                        $deductibleByRate[$key] = ['base' => 0, 'vat' => 0, 'count' => 0, 'rate' => $vatRate];
                    }
                    $deductibleByRate[$key]['base'] += (float) ($line->subtotal ?? 0);
                    $deductibleByRate[$key]['vat'] += (float) ($line->vat_amount ?? 0);
                    $deductibleByRate[$key]['count']++;
                }
            }
        }

        // Totaux
        $vatCollectedNormal = $collectedByRate['T']['vat'] ?? 0;
        $vatCollectedReduced = ($collectedByRate['R']['vat'] ?? 0) + ($collectedByRate['S']['vat'] ?? 0);
        $vatCollectedOther = 0;
        $vatCollectedTotal = $vatCollectedNormal + $vatCollectedReduced;

        $vatDeductibleNormal = $deductibleByRate['T']['vat'] ?? 0;
        $vatDeductibleReduced = ($deductibleByRate['R']['vat'] ?? 0) + ($deductibleByRate['S']['vat'] ?? 0);
        $vatDeductibleImmobilisations = 0;
        $vatDeductibleOther = 0;
        $vatDeductibleTotal = $vatDeductibleNormal + $vatDeductibleReduced;

        $vatNet = $vatCollectedTotal - $vatDeductibleTotal;
        $previousCredit = $this->getPreviousCredit($clientId, $periodType, $year, $month, $quarter);
        $netToPay = max(0, $vatNet - $previousCredit);
        $vatCredit = max(0, $previousCredit - $vatNet);

        return [
            'period_type' => $periodType,
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'due_date' => $dueDate,
            'vat_collected_normal' => $vatCollectedNormal,
            'vat_collected_reduced' => $vatCollectedReduced,
            'vat_collected_other' => $vatCollectedOther,
            'vat_collected_total' => $vatCollectedTotal,
            'vat_deductible_normal' => $vatDeductibleNormal,
            'vat_deductible_reduced' => $vatDeductibleReduced,
            'vat_deductible_immobilisations' => $vatDeductibleImmobilisations,
            'vat_deductible_other' => $vatDeductibleOther,
            'vat_deductible_total' => $vatDeductibleTotal,
            'vat_net' => $vatNet,
            'vat_payable' => max(0, $vatNet),
            'vat_credit' => $vatCredit,
            'previous_credit' => $previousCredit,
            'net_to_pay' => $netToPay,
            'collectedByRate' => $collectedByRate,
            'deductibleByRate' => $deductibleByRate,
            'collectedInvoices' => $collectedInvoices,
            'deductibleInvoices' => $deductibleInvoices,
        ];
    }

    /**
     * Crée la déclaration de TVA
     */
    public function createDeclaration(int $clientId, array $data): VatDeclaration
    {
        return DB::transaction(function () use ($clientId, $data) {
            // Générer le numéro
            $prefix = 'TVA-' . $data['year'];
            if ($data['period_type'] === 'monthly') {
                $prefix .= '-' . str_pad((string) $data['month'], 2, '0', STR_PAD_LEFT);
            } elseif ($data['period_type'] === 'quarterly') {
                $prefix .= '-Q' . $data['quarter'];
            }
            $count = VatDeclaration::where('client_id', $clientId)
                ->where('declaration_number', 'like', $prefix . '%')
                ->count() + 1;
            $declarationNumber = $prefix . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);

            // Calculer la déclaration
            $computed = $this->computeDeclaration(
                $clientId,
                $data['period_type'],
                $data['year'],
                $data['month'] ?? null,
                $data['quarter'] ?? null
            );

            // Créer la déclaration
            $declaration = VatDeclaration::create([
                'client_id' => $clientId,
                'declaration_number' => $declarationNumber,
                'period_type' => $computed['period_type'],
                'year' => $computed['year'],
                'month' => $computed['month'],
                'quarter' => $computed['quarter'],
                'start_date' => $computed['start_date'],
                'end_date' => $computed['end_date'],
                'due_date' => $computed['due_date'],
                'vat_collected_normal' => $computed['vat_collected_normal'],
                'vat_collected_reduced' => $computed['vat_collected_reduced'],
                'vat_collected_other' => $computed['vat_collected_other'],
                'vat_collected_total' => $computed['vat_collected_total'],
                'vat_deductible_normal' => $computed['vat_deductible_normal'],
                'vat_deductible_reduced' => $computed['vat_deductible_reduced'],
                'vat_deductible_immobilisations' => $computed['vat_deductible_immobilisations'],
                'vat_deductible_other' => $computed['vat_deductible_other'],
                'vat_deductible_total' => $computed['vat_deductible_total'],
                'vat_net' => $computed['vat_net'],
                'vat_payable' => $computed['vat_payable'],
                'vat_credit' => $computed['vat_credit'],
                'previous_credit' => $computed['previous_credit'],
                'net_to_pay' => $computed['net_to_pay'],
                'status' => VatDeclaration::STATUS_COMPUTED,
                'created_by' => Auth::id(),
            ]);

            // Créer les lignes par taux
            foreach ($computed['collectedByRate'] as $code => $lineData) {
                VatDeclarationLine::create([
                    'client_id' => $clientId,
                    'declaration_id' => $declaration->id,
                    'type' => 'collected',
                    'vat_code' => $code,
                    'vat_rate' => $lineData['rate'],
                    'base_amount' => $lineData['base'],
                    'vat_amount' => $lineData['vat'],
                    'invoice_count' => $lineData['count'],
                ]);
            }

            foreach ($computed['deductibleByRate'] as $code => $lineData) {
                VatDeclarationLine::create([
                    'client_id' => $clientId,
                    'declaration_id' => $declaration->id,
                    'type' => 'deductible',
                    'vat_code' => $code,
                    'vat_rate' => $lineData['rate'],
                    'base_amount' => $lineData['base'],
                    'vat_amount' => $lineData['vat'],
                    'invoice_count' => $lineData['count'],
                ]);
            }

            // Lier les factures
            foreach ($computed['collectedInvoices'] as $invoice) {
                VatDeclarationInvoice::create([
                    'client_id' => $clientId,
                    'declaration_id' => $declaration->id,
                    'invoice_id' => $invoice->id,
                    'type' => 'collected',
                    'base_amount' => (float) $invoice->subtotal,
                    'vat_amount' => (float) $invoice->vat_total,
                ]);
            }

            foreach ($computed['deductibleInvoices'] as $invoice) {
                VatDeclarationInvoice::create([
                    'client_id' => $clientId,
                    'declaration_id' => $declaration->id,
                    'invoice_id' => $invoice->id,
                    'type' => 'deductible',
                    'base_amount' => (float) $invoice->subtotal,
                    'vat_amount' => (float) $invoice->vat_total,
                ]);
            }

            return $declaration->fresh(['lines', 'declarationInvoices.invoice']);
        });
    }

    /**
     * Soumettre la déclaration et générer l'écriture comptable
     */
    public function submitDeclaration(string $declarationId): VatDeclaration
    {
        return DB::transaction(function () use ($declarationId) {
            $clientId = $this->getClientId();

            $declaration = VatDeclaration::where('id', $declarationId)
                ->where('client_id', $clientId)
                ->firstOrFail();

            if ($declaration->status !== VatDeclaration::STATUS_COMPUTED) {
                throw ValidationException::withMessages([
                    'status' => 'Seules les déclarations calculées peuvent être soumises.',
                ]);
            }

            // Journal OD (Opérations Diverses)
            $journal = Journal::where('client_id', $clientId)
                ->where('code', 'OD')
                ->firstOrFail();

            // Comptes SYSCOHADA : 4411 TVA collectée, 4412 TVA déductible, 4413 TVA due
            $collectAccount = $this->ensureAccountExists($clientId, '4411', 'TVA collectée', 'liability');
            $deductAccount = $this->ensureAccountExists($clientId, '4412', 'TVA déductible', 'asset');
            $vatPayableAccount = $this->ensureAccountExists($clientId, '4413', 'TVA due', 'liability');

            // Construire les lignes d'écriture
            $lines = [];

            // Extourne du compte TVA collectée (4411) — on le débite
            if ($declaration->vat_collected_total > 0) {
                $lines[] = [
                    'account_id' => $collectAccount->id,
                    'description' => "Extourne TVA collectée {$declaration->period_label}",
                    'debit' => (float) $declaration->vat_collected_total,
                    'credit' => 0,
                ];
            }

            // Solde du compte TVA déductible (4412) — on le crédite
            if ($declaration->vat_deductible_total > 0) {
                $lines[] = [
                    'account_id' => $deductAccount->id,
                    'description' => "TVA déductible {$declaration->period_label}",
                    'debit' => 0,
                    'credit' => (float) $declaration->vat_deductible_total,
                ];
            }

            // TVA nette à payer (4413)
            if ($declaration->net_to_pay > 0) {
                $lines[] = [
                    'account_id' => $vatPayableAccount->id,
                    'description' => "TVA à payer {$declaration->period_label}",
                    'debit' => 0,
                    'credit' => (float) $declaration->net_to_pay,
                ];
            }

            // Crédit de TVA reportable (4414)
            if ($declaration->vat_credit > 0) {
                $creditAccount = $this->ensureAccountExists($clientId, '4414', 'Crédit de TVA', 'asset');

                if ($creditAccount) {
                    $lines[] = [
                        'account_id' => $creditAccount->id,
                        'description' => "Crédit de TVA reportable {$declaration->period_label}",
                        'debit' => (float) $declaration->vat_credit,
                        'credit' => 0,
                    ];
                }
            }

            if (!empty($lines)) {
                $entryData = [
                    'journal_id' => $journal->id,
                    'entry_date' => $declaration->end_date->format('Y-m-d'),
                    'value_date' => $declaration->end_date->format('Y-m-d'),
                    'reference' => $declaration->declaration_number,
                    'description' => "Déclaration TVA {$declaration->period_label}",
                    'lines' => $lines,
                ];

                $entry = $this->entryService->createEntry($entryData);
                $this->entryService->postEntry($entry->id);

                $declaration->journal_entry_id = $entry->id;
            }

            $declaration->status = VatDeclaration::STATUS_SUBMITTED;
            $declaration->validated_by = Auth::id();
            $declaration->validated_at = now();
            $declaration->save();

            return $declaration->fresh(['lines', 'declarationInvoices.invoice', 'journalEntry']);
        });
    }

    /**
     * Enregistre le paiement de la TVA
     */
    public function payDeclaration(string $declarationId, array $paymentData): VatDeclaration
    {
        return DB::transaction(function () use ($declarationId, $paymentData) {
            $clientId = $this->getClientId();

            $declaration = VatDeclaration::where('id', $declarationId)
                ->where('client_id', $clientId)
                ->firstOrFail();

            if ($declaration->status !== VatDeclaration::STATUS_SUBMITTED) {
                throw ValidationException::withMessages([
                    'status' => 'Seules les déclarations soumises peuvent être payées.',
                ]);
            }

            if ($declaration->net_to_pay <= 0) {
                throw ValidationException::withMessages([
                    'net_to_pay' => 'Aucun montant à payer (net_to_pay = 0).',
                ]);
            }

            // Compte TVA à payer (4413)
            $vatPayableAccount = $this->ensureAccountExists($clientId, '4413', 'TVA due', 'liability');

            // Compte banque (521 par défaut)
            $bankAccountId = $paymentData['bank_account_id']
                ?? $this->ensureAccountExists($clientId, '521', 'Banque', 'asset')->id;

            // Journal BQ
            $journal = Journal::where('client_id', $clientId)
                ->where('code', 'BQ')
                ->firstOrFail();

            $paymentEntryData = [
                'journal_id' => $journal->id,
                'entry_date' => $paymentData['payment_date'] ?? now()->format('Y-m-d'),
                'value_date' => $paymentData['payment_date'] ?? now()->format('Y-m-d'),
                'reference' => 'PAIEMENT-' . $declaration->declaration_number,
                'description' => "Paiement TVA {$declaration->period_label}",
                'lines' => [
                    [
                        'account_id' => $vatPayableAccount->id,
                        'description' => "Règlement TVA {$declaration->period_label}",
                        'debit' => (float) $declaration->net_to_pay,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $bankAccountId,
                        'description' => "Règlement TVA {$declaration->period_label}",
                        'debit' => 0,
                        'credit' => (float) $declaration->net_to_pay,
                    ],
                ],
            ];

            $entry = $this->entryService->createEntry($paymentEntryData);
            $this->entryService->postEntry($entry->id);

            $declaration->payment_journal_entry_id = $entry->id;
            $declaration->status = VatDeclaration::STATUS_PAID;
            $declaration->payment_date = $paymentData['payment_date'] ?? now();
            $declaration->save();

            return $declaration->fresh();
        });
    }

    /**
     * Détermine les dates de période
     */
    private function getPeriodDates(string $periodType, int $year, ?int $month = null, ?int $quarter = null): array
    {
        return match($periodType) {
            'monthly' => [
                "{$year}-" . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . "-01",
                date('Y-m-t', strtotime("{$year}-{$month}-01")),
                date('Y-m-d', strtotime("+15 days", strtotime("{$year}-{$month}-01"))),
            ],
            'quarterly' => [
                "{$year}-" . str_pad((string) (($quarter * 3) - 2), 2, '0', STR_PAD_LEFT) . "-01",
                date('Y-m-t', strtotime("{$year}-" . ($quarter * 3) . "-01")),
                "{$year}-" . str_pad((string) (($quarter * 3) + 1), 2, '0', STR_PAD_LEFT) . "-15",
            ],
            'yearly' => [
                "{$year}-01-01",
                "{$year}-12-31",
                ($year + 1) . "-04-15",
            ],
            default => throw new \InvalidArgumentException("Période invalide: {$periodType}"),
        };
    }

    /**
     * Récupère le crédit de TVA de la période précédente
     */
    private function getPreviousCredit(int $clientId, string $periodType, int $year, ?int $month = null, ?int $quarter = null): float
    {
        $prevDeclaration = null;

        if ($periodType === 'monthly' && $month > 1) {
            $prevDeclaration = VatDeclaration::where('client_id', $clientId)
                ->where('period_type', 'monthly')
                ->where('year', $year)
                ->where('month', $month - 1)
                ->where('status', VatDeclaration::STATUS_PAID)
                ->latest()
                ->first();
        } elseif ($periodType === 'quarterly') {
            $prevQ = $quarter > 1 ? $quarter - 1 : 4;
            $prevY = $quarter > 1 ? $year : $year - 1;
            $prevDeclaration = VatDeclaration::where('client_id', $clientId)
                ->where('period_type', 'quarterly')
                ->where('year', $prevY)
                ->where('quarter', $prevQ)
                ->where('status', VatDeclaration::STATUS_PAID)
                ->latest()
                ->first();
        }

        return $prevDeclaration?->vat_credit ?? 0;
    }

    /**
     * Détermine le code de taux (T/R/S) à partir du pourcentage
     */
    private function getRateCode(float $rate): string
    {
        return match(true) {
            $rate <= 5   => 'S',
            $rate <= 9   => 'R',
            $rate <= 11  => 'R', // 10% entrant aussi dans réduit
            $rate > 11   => 'T',
            default      => 'T',
        };
    }

    /**
     * Récupère ou crée un compte comptable pour le client
     */
    private function ensureAccountExists(int $clientId, string $code, string $defaultName, string $type): AccountingAccount
    {
        // Chercher d'abord chez le client
        $account = AccountingAccount::where('client_id', $clientId)
            ->where('code', $code)
            ->first();

        if ($account) {
            return $account;
        }

        // Chercher dans les comptes globaux SYSCOHADA
        $global = AccountingAccount::where('client_id', 0)
            ->where('code', $code)
            ->first();

        if ($global) {
            return AccountingAccount::create([
                'client_id' => $clientId,
                'code' => $global->code,
                'name' => $global->name,
                'type' => $global->type,
                'is_active' => true,
            ]);
        }

        // Créer avec les valeurs par défaut
        return AccountingAccount::create([
            'client_id' => $clientId,
            'code' => $code,
            'name' => $defaultName,
            'type' => $type,
            'is_active' => true,
        ]);
    }
}
