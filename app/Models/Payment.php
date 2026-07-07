<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        // Champs existants (ventes)
        'sale_id',
        'payment_method',
        'amount',
        'reference',
        'status',
        'gateway_response',
        // Nouveaux champs (facturation)
        'client_id',
        'type',
        'payment_number',
        'partner_id',
        'invoice_id',
        'payment_date',
        'exchange_rate',
        'bank_account_id',
        'journal_entry_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'gateway_response' => 'array',
        ];
    }

    // ─── Relations existantes ─────────────────────────────

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // ─── Nouvelles relations ──────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    // ─── Constantes ───────────────────────────────────────

    const METHODS = [
        'bank_transfer' => 'Virement bancaire',
        'check' => 'Chèque',
        'cash' => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'card' => 'Carte bancaire',
        'direct_debit' => 'Prélèvement',
        'other' => 'Autre',
    ];
}
