<?php

namespace App\Models\Gel\Comptabilite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    protected $table = 'gel_journaux';

    protected $fillable = [
        'cabinet_id',
        'code',
        'libelle',
        'type',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    // ─── Relations ───────────────────────────────────────────────

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Cabinet::class, 'cabinet_id');
    }

    public function ecritures(): HasMany
    {
        return $this->hasMany(EcritureComptable::class, 'journal_id');
    }

    // ─── Accesseurs ──────────────────────────────────────────────

    /**
     * Construit le prochain numéro d'écriture pour ce journal.
     * Format : JJ-AAAA-NNNNN (ex: AC-2026-00001)
     */
    public function getNumeroSuivantAttribute(): string
    {
        $year = now()->format('Y');
        $last = EcritureComptable::where('journal_id', $this->id)
            ->whereYear('date_ecriture', $year)
            ->orderBy('id', 'desc')
            ->first();

        $next = $last ? intval(substr($last->numero, -5)) + 1 : 1;

        return sprintf('%s-%s-%05d', $this->code, $year, $next);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeByCabinet($query, int $cabinetId)
    {
        return $query->where('cabinet_id', $cabinetId);
    }
}
