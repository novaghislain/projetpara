<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un compte bancaire.
 *
 * Chaque compte bancaire est lié à un client (entreprise) et
 * contient les coordonnées bancaires (RIB, IBAN, SWIFT/BIC).
 * Il est associé à un compte comptable pour le rapprochement
 * automatique. Le solde courant et le solde rapproché assurent
 * le suivi de la trésorerie.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $name Nom du compte
 * @property string $bank_name Nom de la banque
 * @property string $account_number Numéro de compte
 * @property string|null $iban IBAN
 * @property string|null $swift Code SWIFT/BIC
 * @property string $currency Devise
 * @property string $type Type de compte (courant, epargne, etc.)
 * @property int|null $accounting_account_id Compte comptable associé
 * @property float $opening_balance Solde d'ouverture
 * @property string|null $opening_date Date d'ouverture
 * @property float $current_balance Solde courant
 * @property float $reconciled_balance Solde rapproché
 * @property string|null $last_reconciliation_date Dernière date de rapprochement
 * @property string|null $contact_phone Contact téléphonique
 * @property string|null $contact_email Contact email
 * @property string|null $notes Notes
 * @property bool $is_active Compte actif
 * @property bool $is_default Compte par défaut
 *
 * @property-read Client|null $client Client (entreprise) associé
 * @property-read AccountingAccount|null $accountingAccount Compte comptable lié
 * @property-read \Illuminate\Database\Eloquent\Collection|BankTransaction[] $transactions Transactions bancaires
 * @property-read \Illuminate\Database\Eloquent\Collection|BankReconciliation[] $reconciliations Rapprochements bancaires
 *
 * @table bank_accounts
 */
class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'name', 'bank_name', 'account_number',
        'iban', 'swift', 'currency', 'type',
        'accounting_account_id',
        'opening_balance', 'opening_date',
        'current_balance', 'reconciled_balance',
        'last_reconciliation_date',
        'contact_phone', 'contact_email', 'notes',
        'is_active', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
            'last_reconciliation_date' => 'date',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function accountingAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id');
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class, 'bank_account_id');
    }
}
