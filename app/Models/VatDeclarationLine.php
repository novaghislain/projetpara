<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle VatDeclarationLine - Ligne d'une déclaration de TVA.
 *
 * Table associée : 'vat_declaration_lines' (convention Laravel).
 * Détail d'une déclaration par taux de TVA : base imposable, montant de TVA,
 * et nombre de factures associées.
 * Relations :
 * - declaration() : appartient à une déclaration (VatDeclaration).
 */
class VatDeclarationLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'declaration_id',
        'type', 'vat_code', 'vat_rate',
        'base_amount', 'vat_amount', 'invoice_count',
    ];

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(VatDeclaration::class, 'declaration_id');
    }
}
