<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant un paiement sur une facture.
 *
 * Table associée : `company_payments` (via convention Laravel)
 *
 * Relations :
 * - Un paiement appartient à une facture (CompanyInvoice)
 */
class CompanyPayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'date',
        'amount',
        'method',
        'reference',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CompanyInvoice::class, 'invoice_id');
    }
}
