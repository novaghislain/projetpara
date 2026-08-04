<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle VatDeclaration - Déclaration de TVA (système SYSCOHADA).
 *
 * Table associée : 'vat_declarations' (convention Laravel).
 * Gère les déclarations périodiques (mensuelle/trimestrielle/annuelle)
 * de TVA avec ventilation des montants collectés et déductibles,
 * report de crédit antérieur, et écritures comptables associées.
 * Relations :
 * - client() : appartient à un client (Client).
 * - lines() : a plusieurs lignes de TVA (VatDeclarationLine).
 * - declarationInvoices() : a plusieurs factures associées (VatDeclarationInvoice).
 * - journalEntry() : écriture de déclaration (JournalEntry).
 * - paymentJournalEntry() : écriture de paiement (JournalEntry).
 */
class VatDeclaration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'declaration_number', 'period_type', 'year', 'month', 'quarter',
        'start_date', 'end_date', 'due_date', 'payment_date',
        'vat_collected_normal', 'vat_collected_reduced', 'vat_collected_other',
        'vat_collected_total',
        'vat_deductible_normal', 'vat_deductible_reduced',
        'vat_deductible_immobilisations', 'vat_deductible_other',
        'vat_deductible_total',
        'vat_net', 'vat_payable', 'vat_credit',
        'previous_credit', 'net_to_pay',
        'status', 'journal_entry_id', 'payment_journal_entry_id',
        'created_by', 'validated_by', 'validated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'due_date' => 'date',
            'payment_date' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(VatDeclarationLine::class, 'declaration_id');
    }

    public function declarationInvoices(): HasMany
    {
        return $this->hasMany(VatDeclarationInvoice::class, 'declaration_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function paymentJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'payment_journal_entry_id');
    }

    public function getPeriodLabelAttribute(): string
    {
        return match($this->period_type) {
            'monthly' => "{$this->year}-" . str_pad((string) $this->month, 2, '0', STR_PAD_LEFT),
            'quarterly' => "T{$this->quarter} {$this->year}",
            'yearly' => "Année {$this->year}",
            default => "{$this->year}",
        };
    }

    const STATUS_DRAFT = 'draft';
    const STATUS_COMPUTED = 'computed';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    const PERIOD_TYPES = ['monthly', 'quarterly', 'yearly'];
}
