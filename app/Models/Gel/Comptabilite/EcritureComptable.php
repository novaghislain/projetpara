<?php

namespace App\Models\Gel\Comptabilite;

use App\Models\Cabinet;
use App\Models\Client;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Modèle EcritureComptable (Écriture comptable).
 *
 * Représente une écriture comptable complète dans le module GEL/Comptabilite.
 * Chaque écriture appartient à un journal, un exercice et un cabinet,
 * et contient plusieurs lignes d'écriture (débit/crédit).
 * Elle doit être équilibrée (total débit = total crédit) et peut être validée.
 * Table associée : `gel_ecritures`.
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property int $exercice_id ID de l'exercice comptable
 * @property int $journal_id ID du journal
 * @property int|null $client_id ID du client
 * @property string|null $numero Numéro unique de l'écriture (format: CODE-ANNEE-NNNNN)
 * @property \Carbon\Carbon $date_ecriture Date comptable
 * @property \Carbon\Carbon|null $date_piece Date de la pièce justificative
 * @property string|null $reference_piece Référence de la pièce justificative
 * @property string|null $libelle Libellé de l'écriture
 * @property float $total_debit Total au débit
 * @property float $total_credit Total au crédit
 * @property bool $valide Si l'écriture est validée
 * @property int|null $valide_par ID de l'utilisateur validateur
 * @property \Carbon\Carbon|null $date_validation Date de validation
 * @property int $created_by ID du créateur
 *
 * @property-read \App\Models\Cabinet $cabinet Cabinet associé
 * @property-read \App\Models\Gel\Comptabilite\ExerciceComptable $exercice Exercice comptable
 * @property-read \App\Models\Gel\Comptabilite\Journal $journal Journal comptable
 * @property-read \App\Models\Client|null $client Client associé
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\Comptabilite\LigneEcriture[] $lignes Lignes d'écriture
 * @property-read \App\Models\User|null $validateur Utilisateur validateur
 * @property-read \App\Models\User|null $createur Utilisateur créateur
 */
class EcritureComptable extends Model
{
    protected $table = 'gel_ecritures';

    protected $fillable = [
        'cabinet_id',
        'exercice_id',
        'journal_id',
        'client_id',
        'numero',
        'date_ecriture',
        'date_piece',
        'reference_piece',
        'libelle',
        'total_debit',
        'total_credit',
        'valide',
        'valide_par',
        'date_validation',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_ecriture' => 'date',
            'date_piece' => 'date',
            'date_validation' => 'datetime',
            'valide' => 'boolean',
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
        ];
    }

    // ─── Relations ───────────────────────────────────────────────

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function exercice(): BelongsTo
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneEcriture::class, 'ecriture_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Méthodes métier ─────────────────────────────────────────

    /**
     * Vérifie si l'écriture est équilibrée (total débit = total crédit).
     */
    public function estEquilibree(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    /**
     * Calcule le total des lignes au débit.
     */
    public function totalLignesDebit(): float
    {
        return (float) $this->lignes()
            ->where('sens', 'debit')
            ->sum('montant');
    }

    /**
     * Calcule le total des lignes au crédit.
     */
    public function totalLignesCredit(): float
    {
        return (float) $this->lignes()
            ->where('sens', 'credit')
            ->sum('montant');
    }

    /**
     * Valide l'écriture comptable.
     */
    public function valider(int $userId): void
    {
        if ($this->valide) {
            throw new \RuntimeException("Cette écriture est déjà validée.");
        }

        if (!$this->estEquilibree()) {
            throw new \RuntimeException(
                "Impossible de valider : l'écriture n'est pas équilibrée " .
                "(Débit: {$this->total_debit}, Crédit: {$this->total_credit})."
            );
        }

        $this->update([
            'valide' => true,
            'valide_par' => $userId,
            'date_validation' => now(),
        ]);
    }

    /**
     * Vérifie si l'écriture est validée.
     */
    public function estValidee(): bool
    {
        return $this->valide;
    }

    /**
     * Vérifie si l'écriture peut être modifiée (non validée uniquement).
     */
    public function peutEtreModifiee(): bool
    {
        return !$this->valide;
    }

    /**
     * Génère un PDF de l'écriture comptable.
     */
    public function pdf(): \Barryvdh\DomPDF\PDF
    {
        $this->loadMissing(['lignes.compte', 'journal', 'exercice', 'validateur', 'createur']);

        $pdf = Pdf::loadView('pdfs.ecriture-comptable', [
            'ecriture' => $this,
        ]);

        return $pdf->setPaper('a4', 'portrait');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeValide($query)
    {
        return $query->where('valide', true);
    }

    public function scopeNonValide($query)
    {
        return $query->where('valide', false);
    }

    public function scopeByCabinet($query, int $cabinetId)
    {
        return $query->where('cabinet_id', $cabinetId);
    }

    public function scopeByExercice($query, int $exerciceId)
    {
        return $query->where('exercice_id', $exerciceId);
    }

    public function scopeByJournal($query, int $journalId)
    {
        return $query->where('journal_id', $journalId);
    }

    public function scopeByPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_ecriture', [$dateDebut, $dateFin]);
    }

    // ─── Boot ────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $ecriture) {
            if (!$ecriture->numero) {
                $journal = Journal::find($ecriture->journal_id);
                if ($journal) {
                    $ecriture->numero = $journal->numero_suivant;
                }
            }
        });
    }
}
