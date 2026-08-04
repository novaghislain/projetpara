<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une écriture de clôture comptable.
 *
 * Les écritures de clôture sont générées en fin d'exercice fiscal pour
 * solder les comptes de gestion et calculer le résultat. Elles sont liées
 * à un client, un exercice fiscal et un journal comptable.
 * Le champ `entries` stocke les écritures au format JSON.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property int|null $fiscal_year_id Identifiant de l'exercice fiscal
 * @property string $reference Référence de l'écriture de clôture
 * @property string $type Type de clôture (resultat, bilan, etc.)
 * @property string|null $description Description de l'opération
 * @property array $entries Écritures comptables (stockées en JSON)
 * @property string $status Statut (brouillon, valide, comptabilise)
 * @property int|null $journal_id Identifiant du journal comptable
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 * @property int|null $validated_by Identifiant du validateur
 * @property string|null $validated_at Date de validation
 *
 * @property-read Client|null $client Client (entreprise) associé
 * @property-read FiscalYear|null $fiscalYear Exercice fiscal associé
 * @property-read AccountingJournal|null $journal Journal comptable associé
 * @property-read User|null $createdBy Utilisateur créateur
 * @property-read User|null $validatedBy Utilisateur validateur
 *
 * @table accounting_closing_entries
 */
class AccountingClosingEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'fiscal_year_id', 'reference', 'type',
        'description', 'entries', 'status',
        'journal_id', 'created_by', 'validated_by', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'entries' => 'json',
            'validated_at' => 'datetime',
        ];
    }

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(AccountingJournal::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeValide($query)
    {
        return $query->where('status', 'valide');
    }

    public function scopeComptabilise($query)
    {
        return $query->where('status', 'comptabilise');
    }
}
