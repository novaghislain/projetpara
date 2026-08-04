<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Partner (Partenaire / Tiers).
 *
 * Gère les clients et fournisseurs (tiers) avec leurs coordonnées,
 * informations fiscales (IFU, RCCM), conditions commerciales,
 * limites de crédit, et comptes comptables par défaut.
 * Un partenaire peut être à la fois client et fournisseur.
 *
 * @property int $id
 * @property int $client_id ID du client propriétaire
 * @property string $type Type (customer, supplier, both)
 * @property string $code Code partenaire
 * @property string|null $company_name Raison sociale
 * @property string|null $last_name Nom (personne physique)
 * @property string|null $first_name Prénom (personne physique)
 * @property string|null $email Email
 * @property string|null $phone Téléphone fixe
 * @property string|null $mobile Téléphone mobile
 * @property string|null $website Site web
 * @property string|null $tax_id IFU (Identifiant Fiscal Unique)
 * @property string|null $rccm Numéro RCCM
 * @property string|null $address Adresse
 * @property string|null $city Ville
 * @property string|null $country Pays
 * @property string|null $postal_code Code postal
 * @property string $currency Devise par défaut
 * @property float|null $credit_limit Limite de crédit
 * @property int $payment_term_days Délai de paiement (jours)
 * @property string|null $payment_method Méthode de paiement par défaut
 * @property string|null $notes Notes
 * @property string|null $iban IBAN
 * @property string|null $swift Code SWIFT
 * @property string $status Statut (actif, inactif)
 * @property int|null $account_receivable_id Compte comptable client (411)
 * @property int|null $account_payable_id Compte comptable fournisseur (401)
 *
 * @property-read \App\Models\Client $client Client propriétaire
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices Factures
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payments Paiements
 * @property-read \App\Models\AccountingAccount|null $accountReceivable Compte client
 * @property-read \App\Models\AccountingAccount|null $accountPayable Compte fournisseur
 */
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
