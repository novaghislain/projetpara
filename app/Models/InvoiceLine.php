<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'invoice_id', 'line_number',
        'description', 'product_code', 'quantity', 'unit',
        'unit_price', 'discount', 'discount_percent', 'net_unit_price',
        'subtotal', 'vat_code', 'vat_rate', 'vat_amount', 'total',
        'account_id', 'vat_account_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'net_unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'account_id');
    }

    public function vatAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'vat_account_id');
    }
}
