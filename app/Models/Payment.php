<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle Payment (Paiement).
 *
 * Enregistre les paiements reçus ou effectués, liés à une vente
 * ou une facture. Supporte plusieurs méthodes de paiement
 * (virement, chèque, espèces, Mobile Money, carte, prélèvement)
 * et la création d'écritures comptables associées.
 *
 * @property int $id
 * @property int|null $sale_id ID de la vente (legacy)
 * @property string|null $payment_method Méthode de paiement
 * @property float $amount Montant
 * @property string|null $reference Référence
 * @property string|null $status Statut
 * @property array|null $gateway_response Réponse de la passerelle (JSON)
 * @property int|null $client_id ID du client
 * @property string|null $type Type (incoming, outgoing)
 * @property string|null $payment_number Numéro de paiement
 * @property int|null $partner_id ID du partenaire
 * @property int|null $invoice_id ID de la facture
 * @property \Carbon\Carbon|null $payment_date Date de paiement
 * @property float|null $exchange_rate Taux de change
 * @property int|null $bank_account_id ID du compte bancaire
 * @property int|null $journal_entry_id ID de l'écriture comptable
 * @property string|null $notes Notes
 *
 * @property-read \App\Models\Sale|null $sale Vente associée
 * @property-read \App\Models\Client|null $client Client associé
 * @property-read \App\Models\Partner|null $partner Partenaire associé
 * @property-read \App\Models\Invoice|null $invoice Facture associée
 * @property-read \App\Models\JournalEntry|null $journalEntry Écriture comptable
 */
class Payment extends Model
{
    protected $fillable = [
        // Champs existants (ventes)
        'sale_id',
        'payment_method',
        'amount',
        'reference',
        'status',
        'gateway_response',
        // Nouveaux champs (facturation)
        'client_id',
        'type',
        'payment_number',
        'partner_id',
        'invoice_id',
        'payment_date',
        'exchange_rate',
        'bank_account_id',
        'journal_entry_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'gateway_response' => 'array',
        ];
    }

    // ─── Relations existantes ─────────────────────────────

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // ─── Nouvelles relations ──────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    // ─── Constantes ───────────────────────────────────────

    const METHODS = [
        'bank_transfer' => 'Virement bancaire',
        'check' => 'Chèque',
        'cash' => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'card' => 'Carte bancaire',
        'direct_debit' => 'Prélèvement',
        'other' => 'Autre',
    ];
}
