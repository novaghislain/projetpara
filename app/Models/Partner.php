<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'type', 'code', 'company_name',
        'last_name', 'first_name', 'email', 'phone', 'mobile', 'website',
        'tax_id', 'rccm', 'address', 'city', 'country', 'postal_code',
        'currency', 'credit_limit', 'payment_term_days', 'payment_method',
        'notes', 'iban', 'swift', 'status',
        'account_receivable_id', 'account_payable_id',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function accountReceivable(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'account_receivable_id');
    }

    public function accountPayable(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'account_payable_id');
    }

    // ─── Accesseurs ───────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        if ($this->company_name) {
            return $this->company_name;
        }
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeCustomers($query)
    {
        return $query->whereIn('type', ['customer', 'both']);
    }

    public function scopeSuppliers($query)
    {
        return $query->whereIn('type', ['supplier', 'both']);
    }
}
