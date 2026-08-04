<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une ligne d'écriture dans une pièce comptable.
 *
 * Table associée : `entry_lines` (via convention Laravel)
 *
 * Relations :
 * - Une ligne appartient à un client (Client)
 * - Une ligne appartient à une pièce comptable (JournalEntry)
 * - Une ligne est liée à un compte comptable (AccountingAccount)
 */
class EntryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'entry_id',
        'line_number',
        'account_id',
        'account_code',
        'account_label',
        'description',
        'debit',
        'credit',
        'currency',
        'exchange_rate',
        'partner_id',
        'partner_type',
        'lettering_id',
        'vat_code',
        'vat_base',
        'vat_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'vat_base' => 'decimal:2',
            'vat_amount' => 'decimal:2',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'account_id');
    }
}
