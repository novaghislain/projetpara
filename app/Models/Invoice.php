<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Invoice (Facture).
 *
 * Gère les factures clients et fournisseurs, les avoirs et notes de débit.
 * Contient les lignes de facturation, les paiements associés,
 * la TVA, les remises et le suivi du statut (brouillon, envoyée, payée, etc.).
 * Supporte les écritures comptables liées via JournalEntry.
 *
 * @property int $id
 * @property int $client_id ID du client propriétaire
 * @property string $type Type (customer_invoice, supplier_invoice, credit_note, debit_note)
 * @property string $invoice_number Numéro unique de facture
 * @property int|null $partner_id ID du partenaire (client/fournisseur)
 * @property string|null $partner_name Nom du partenaire (dénormalisé)
 * @property string|null $partner_tax_id IFU du partenaire
 * @property string|null $partner_address Adresse du partenaire
 * @property \Carbon\Carbon $invoice_date Date de facture
 * @property \Carbon\Carbon $due_date Date d'échéance
 * @property \Carbon\Carbon|null $delivery_date Date de livraison
 * @property string|null $payment_term Conditions de paiement
 * @property string|null $payment_method Méthode de paiement
 * @property string $status Statut (draft, sent, confirmed, partially_paid, paid, overdue, cancelled, credit_note)
 * @property int|null $related_invoice_id ID de la facture liée (avoir)
 * @property string $currency Devise
 * @property float $exchange_rate Taux de change
 * @property float $subtotal Sous-total HT
 * @property float $discount Remise globale
 * @property float|null $discount_percent Pourcentage de remise
 * @property float $tax_base Base taxable
 * @property float $vat_total Total TVA
 * @property float $total Total TTC
 * @property float $paid_amount Montant payé
 * @property float $balance_due Solde dû
 * @property string|null $notes Notes
 * @property string|null $terms_conditions Conditions générales
 * @property int|null $journal_entry_id ID de l'écriture comptable liée
 * @property int $created_by ID du créateur
 * @property int|null $validated_by ID du validateur
 * @property \Carbon\Carbon|null $validated_at Date de validation
 *
 * @property-read \App\Models\Client $client Client propriétaire
 * @property-read \App\Models\Partner|null $partner Partenaire associé
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceLine[] $lines Lignes de facture
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payments Paiements
 * @property-read \App\Models\JournalEntry|null $journalEntry Écriture comptable liée
 * @property-read \App\Models\Invoice|null $relatedInvoice Facture liée
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $creditNotes Avoirs liés
 * @property-read \App\Models\User $creator Utilisateur créateur
 * @property-read \App\Models\User|null $validator Utilisateur validateur
 */
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'type', 'invoice_number',
        'partner_id', 'partner_name', 'partner_tax_id', 'partner_address',
        'invoice_date', 'due_date', 'delivery_date',
        'payment_term', 'payment_method',
        'status', 'related_invoice_id',
        'currency', 'exchange_rate',
        'subtotal', 'discount', 'discount_percent',
        'tax_base', 'vat_total', 'total',
        'paid_amount', 'balance_due',
        'notes', 'terms_conditions',
        'journal_entry_id', 'created_by', 'validated_by', 'validated_at',
        'emecef_nim', 'emecef_compteur', 'emecef_hash',
        'emecef_qr', 'emecef_statut', 'emecef_datetime',
        'emecef_uid', 'emecef_response',
        'emecef_is_simulation',
        'aib_base', 'aib_rate', 'aib_amount',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'delivery_date' => 'date',
            'validated_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_base' => 'decimal:2',
            'vat_total' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'emecef_datetime' => 'datetime',
            'emecef_response' => 'array',
            'aib_base' => 'decimal:2',
            'aib_rate' => 'decimal:2',
            'aib_amount' => 'decimal:2',
            'emecef_is_simulation' => 'boolean',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('line_number');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function relatedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(Invoice::class, 'related_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ─── Méthodes ─────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->balance_due <= 0;
    }

    public function isOverdue(): bool
    {
        return !$this->isPaid() && $this->due_date < now();
    }

    // ─── Constantes ───────────────────────────────────────

    const TYPES = [
        'customer_invoice' => 'Facture client',
        'supplier_invoice' => 'Facture fournisseur',
        'credit_note' => 'Avoir',
        'debit_note' => 'Note de débit',
    ];

    const STATUS = [
        'draft' => 'Brouillon',
        'sent' => 'Envoyée',
        'confirmed' => 'Confirmée',
        'partially_paid' => 'Partiellement payée',
        'paid' => 'Payée',
        'overdue' => 'En retard',
        'cancelled' => 'Annulée',
        'credit_note' => 'Avoir',
    ];
}
