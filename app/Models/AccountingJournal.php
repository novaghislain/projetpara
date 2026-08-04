<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un journal comptable (écriture comptable).
 *
 * Chaque écriture est liée à un client (entreprise) et à un exercice fiscal.
 * Elle contient des lignes (AccountingJournalLine) au débit et au crédit.
 * Un journal peut être une contre-passation (is_reversal) d'un autre journal.
 * Le statut suit le cycle de vie : brouillon -> posted (validé).
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $journal_type Type de journal (ventes, achats, banque, caisse, operations_diverses)
 * @property string $entry_date Date de l'écriture
 * @property string $reference Référence de l'écriture
 * @property string|null $description Description de l'écriture
 * @property string $status Statut (draft, posted)
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 * @property int|null $fiscal_year_id Identifiant de l'exercice fiscal
 * @property string|null $numero_piece Numéro de pièce comptable
 * @property int|null $validated_by Identifiant du validateur
 * @property string|null $validated_at Date de validation
 * @property bool $is_reversal Indique si c'est une contre-passation
 * @property int|null $reversed_journal_id Identifiant du journal contre-passé
 * @property string|null $source_module Module source de l'écriture
 *
 * @property-read Client|null $client Client (entreprise) associé
 * @property-read \Illuminate\Database\Eloquent\Collection|AccountingJournalLine[] $lines Lignes d'écritures (débit/crédit)
 * @property-read User|null $createdBy Utilisateur créateur
 * @property-read FiscalYear|null $fiscalYear Exercice fiscal associé
 * @property-read User|null $validatedBy Utilisateur validateur
 * @property-read AccountingJournal|null $reversedJournal Journal d'origine contre-passé
 * @property-read \Illuminate\Database\Eloquent\Collection|AccountingJournal[] $reversals Contre-passations de ce journal
 *
 * @table accounting_journals
 */
class AccountingJournal extends Model
{
    protected $fillable = [
        'client_id', 'journal_type', 'entry_date', 'reference',
        'description', 'status', 'created_by',
        'fiscal_year_id', 'numero_piece', 'validated_by', 'validated_at',
        'is_reversal', 'reversed_journal_id', 'source_module',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'validated_at' => 'datetime',
            'is_reversal' => 'boolean',
        ];
    }

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AccountingJournalLine::class, 'journal_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function reversedJournal(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_journal_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(self::class, 'reversed_journal_id');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('journal_type', $type);
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    // Accessors
    public function getDebitTotalAttribute()
    {
        return $this->lines()->sum('debit');
    }

    public function getCreditTotalAttribute()
    {
        return $this->lines()->sum('credit');
    }

    public function getIsBalancedAttribute()
    {
        return abs($this->debit_total - $this->credit_total) < 0.01;
    }
}
