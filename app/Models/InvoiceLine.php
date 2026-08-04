<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle InvoiceLine (Ligne de facture).
 *
 * Représente une ligne individuelle sur une facture.
 * Contient le produit/service, les quantités, les prix unitaires,
 * les remises, la TVA et les comptes comptables associés.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property int $invoice_id ID de la facture parente
 * @property int $line_number Numéro de ligne
 * @property string $description Description de la ligne
 * @property string|null $product_code Code produit
 * @property float $quantity Quantité
 * @property string|null $unit Unité (pièce, heure, jour, etc.)
 * @property float $unit_price Prix unitaire HT
 * @property float $discount Remise ligne
 * @property float|null $discount_percent Pourcentage de remise
 * @property float $net_unit_price Prix unitaire net
 * @property float $subtotal Sous-total HT
 * @property string|null $vat_code Code TVA
 * @property float $vat_rate Taux de TVA (%)
 * @property float $vat_amount Montant TVA
 * @property float $total Total TTC ligne
 * @property int|null $account_id ID du compte comptable de vente
 * @property int|null $vat_account_id ID du compte comptable de TVA
 *
 * @property-read \App\Models\Invoice $invoice Facture parente
 * @property-read \App\Models\AccountingAccount|null $account Compte comptable de vente
 * @property-read \App\Models\AccountingAccount|null $vatAccount Compte comptable de TVA
 */
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
