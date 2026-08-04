<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle JournalEntry (Écriture de journal).
 *
 * Représente une écriture comptable dans un journal, avec ses totaux
 * débit/crédit et son statut (brouillon, publiée, verrouillée, annulée).
 * Peut être classifiée automatiquement par l'IA.
 * Contient plusieurs lignes d'écriture (EntryLine).
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property int $journal_id ID du journal
 * @property int $fiscal_period_id ID de la période fiscale
 * @property string $entry_number Numéro de l'écriture
 * @property \Carbon\Carbon $entry_date Date de l'écriture
 * @property \Carbon\Carbon|null $value_date Date de valeur
 * @property string|null $reference Référence externe
 * @property string|null $description Description
 * @property float $total_debit Total au débit
 * @property float $total_credit Total au crédit
 * @property bool $is_balanced Si l'écriture est équilibrée
 * @property string $status Statut (draft, posted, locked, cancelled)
 * @property bool $classified_by_ai Si classifiée par l'IA
 * @property int $created_by ID du créateur
 * @property int|null $validated_by ID du validateur
 * @property \Carbon\Carbon|null $validated_at Date de validation
 *
 * @property-read \App\Models\Client $client Client associé
 * @property-read \App\Models\Journal $journal Journal associé
 * @property-read \App\Models\FiscalPeriod $fiscalPeriod Période fiscale
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EntryLine[] $lines Lignes d'écriture
 * @property-read \App\Models\User $creator Utilisateur créateur
 * @property-read \App\Models\User|null $validator Utilisateur validateur
 */
class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'journal_id',
        'fiscal_period_id',
        'entry_number',
        'entry_date',
        'value_date',
        'reference',
        'description',
        'total_debit',
        'total_credit',
        'is_balanced',
        'status',
        'classified_by_ai',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'value_date' => 'date',
            'validated_at' => 'datetime',
            'is_balanced' => 'boolean',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function fiscalPeriod(): BelongsTo
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(EntryLine::class, 'entry_id');
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

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    // ─── Status ───────────────────────────────────────────

    const STATUS_DRAFT     = 'draft';
    const STATUS_POSTED    = 'posted';
    const STATUS_LOCKED    = 'locked';
    const STATUS_CANCELLED = 'cancelled';
}
