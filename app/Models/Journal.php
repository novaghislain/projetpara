<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Journal (Journal comptable SYSCOHADA).
 *
 * Définit les journaux comptables selon le plan SYSCOHADA.
 * Types disponibles : Achats, Ventes, Banque, Caisse, Opérations Diverses,
 * Salaires, À Nouveaux, Inventaire.
 * Gère la numérotation séquentielle des écritures par préfixe et année.
 *
 * @property int $id
 * @property int|null $tenant_id ID du tenant
 * @property int $client_id ID du client
 * @property int $fiscal_year_id ID de l'exercice fiscal
 * @property string $code Code du journal (AC, VE, BQ, CA, OD, SA, AN, IN)
 * @property string $label Libellé du journal
 * @property string $type Type de journal
 * @property bool $is_default Journal par défaut
 * @property string $prefix Préfixe de numérotation
 * @property int $next_number Prochain numéro d'écriture
 * @property string|null $description Description
 * @property bool $is_active Si le journal est actif
 * @property int $sort_order Ordre d'affichage
 *
 * @property-read \App\Models\Client $client Client associé
 * @property-read \App\Models\FiscalYear $fiscalYear Exercice fiscal
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\JournalEntry[] $entries Écritures comptables
 */
class Journal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'fiscal_year_id',
        'code',
        'label',
        'type',
        'is_default',
        'prefix',
        'next_number',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    // ─── Accesseurs ───────────────────────────────────────

    public function getNextEntryNumberAttribute(): string
    {
        $year = now()->year;
        $number = str_pad($this->next_number, 5, '0', STR_PAD_LEFT);

        return "{$this->prefix}-{$year}-{$number}";
    }

    // ─── Méthodes ─────────────────────────────────────────

    public function incrementNextNumber(): void
    {
        $this->increment('next_number');
    }

    // ─── Types de journaux SYSCOHADA ──────────────────────

    const TYPES = [
        'achats'              => ['code' => 'AC', 'label' => 'Achats',                'prefix' => 'AC'],
        'ventes'              => ['code' => 'VE', 'label' => 'Ventes',                'prefix' => 'VE'],
        'banque'              => ['code' => 'BQ', 'label' => 'Banque',                'prefix' => 'BQ'],
        'caisse'              => ['code' => 'CA', 'label' => 'Caisse',                'prefix' => 'CA'],
        'operations_diverses' => ['code' => 'OD', 'label' => 'Opérations diverses',   'prefix' => 'OD'],
        'salaires'            => ['code' => 'SA', 'label' => 'Salaires',              'prefix' => 'SA'],
        'a_nouveaux'          => ['code' => 'AN', 'label' => 'À nouveaux',            'prefix' => 'AN'],
        'inventaire'          => ['code' => 'IN', 'label' => 'Inventaire',            'prefix' => 'IN'],
    ];
}
