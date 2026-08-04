<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une ligne de facture.
 *
 * Table associée : `company_invoice_items` (via convention Laravel)
 *
 * Relations :
 * - Une ligne appartient à une facture (CompanyInvoice)
 */
class CompanyInvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'total_ht',
        'total_ttc',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CompanyInvoice::class, 'invoice_id');
    }
}
