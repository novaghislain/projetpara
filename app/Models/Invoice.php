<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'type', 'invoice_number',
        'partner_id', 'partner_name', 'partner_tax_id', 'partner_address',
        'invoice_date', 'due_date', 'delivery_date',
        'payment_term', 'payment_method',
        'status', 'related_invoice_id',
        'currency', 'exchange_rate',
        'subtotal', 'discount', 'discount_percent',
        'tax_base', 'vat_total', 'total',
        'paid_amount', 'balance_due',
        'notes', 'terms_conditions',
        'journal_entry_id', 'created_by', 'validated_by', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'delivery_date' => 'date',
            'validated_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_base' => 'decimal:2',
            'vat_total' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('line_number');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function relatedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(Invoice::class, 'related_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ─── Méthodes ─────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->balance_due <= 0;
    }

    public function isOverdue(): bool
    {
        return !$this->isPaid() && $this->due_date < now();
    }

    // ─── Constantes ───────────────────────────────────────

    const TYPES = [
        'customer_invoice' => 'Facture client',
        'supplier_invoice' => 'Facture fournisseur',
        'credit_note' => 'Avoir',
        'debit_note' => 'Note de débit',
    ];

    const STATUS = [
        'draft' => 'Brouillon',
        'sent' => 'Envoyée',
        'confirmed' => 'Confirmée',
        'partially_paid' => 'Partiellement payée',
        'paid' => 'Payée',
        'overdue' => 'En retard',
        'cancelled' => 'Annulée',
        'credit_note' => 'Avoir',
    ];
}
