<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle VatDeclarationInvoice - Facture liée à une déclaration de TVA.
 *
 * Table associée : 'vat_declaration_invoices' (convention Laravel).
 * Fait le lien entre une déclaration de TVA et les factures qui la composent,
 * avec le type (collectée/déductible) et les montants de base et TVA.
 * Relations :
 * - declaration() : appartient à une déclaration (VatDeclaration).
 * - invoice() : appartient à une facture (Invoice).
 */
class VatDeclarationInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'declaration_id', 'invoice_id',
        'type', 'base_amount', 'vat_amount',
    ];

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(VatDeclaration::class, 'declaration_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
