<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'number',
        'type',
        'status',
        'recipient_name',
        'recipient_address',
        'issue_date',
        'due_date',
        'total_ht',
        'total_tva',
        'total_ttc',
        'paid_amount',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date:Y-m-d',
            'due_date' => 'date:Y-m-d',
            'total_ht' => 'decimal:2',
            'total_tva' => 'decimal:2',
            'total_ttc' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(CompanyInvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CompanyPayment::class, 'invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Calculer le montant restant dû.
     */
    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total_ttc - (float) $this->paid_amount);
    }

    /**
     * Vérifier si la facture est entièrement payée.
     */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    /**
     * Scope pour filtrer par client.
     */
    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
