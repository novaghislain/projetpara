<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Modèle FiscalPeriod (Période fiscale).
 *
 * Représente une période au sein d'un exercice fiscal (ex: mois, trimestre).
 * Chaque période est liée à un exercice (FiscalYear) et peut être ouverte ou clôturée.
 *
 * @property int $id
 * @property int $fiscal_year_id
 * @property string $code Code de la période (ex: M01, T1)
 * @property string $label Libellé de la période
 * @property \Carbon\Carbon $start_date Date de début
 * @property \Carbon\Carbon $end_date Date de fin
 * @property string $status Statut (open/closed)
 * @property bool $is_current Indique si c'est la période courante
 * @property \Carbon\Carbon|null $closed_at Date de clôture
 * @property int|null $closed_by ID de l'utilisateur ayant clôturé
 * @property string|null $notes Notes additionnelles
 *
 * @property-read \App\Models\FiscalYear $fiscalYear Exercice fiscal parent
 * @property-read \App\Models\User|null $closedBy Utilisateur ayant clôturé la période
 */
class FiscalPeriod extends Model
{
    protected $fillable = [
        'fiscal_year_id',
        'code',
        'label',
        'start_date',
        'end_date',
        'status',
        'is_current',
        'closed_at',
        'closed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function close(): void
    {
        $this->status = 'closed';
        $this->closed_at = now();
        $this->closed_by = Auth::id();
        $this->save();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeByFiscalYear($query, int $fiscalYearId)
    {
        return $query->where('fiscal_year_id', $fiscalYearId);
    }
}
