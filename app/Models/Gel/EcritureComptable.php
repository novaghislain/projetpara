<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\ClientScopedModel;

/**
 * Modèle EcritureComptable (Écriture comptable - espace Gel).
 *
 * Représente une écriture comptable dans le module Gel.
 * Contient les entrées de journal avec leurs totaux débit/crédit,
 * et peut être validée par un utilisateur autorisé.
 * Table associée : `gel_ecritures`.
 * Supporte la suppression douce (SoftDeletes).
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property int|null $client_id ID du client
 * @property int $journal_id ID du journal
 * @property int $exercice_id ID de l'exercice
 * @property string|null $numero Numéro de l'écriture
 * @property \Carbon\Carbon $date_ecriture Date de l'écriture
 * @property \Carbon\Carbon|null $date_piece Date de la pièce justificative
 * @property string|null $ref_piece Référence de la pièce
 * @property string|null $libelle Libellé de l'écriture
 * @property float $total_debit Total au débit
 * @property float $total_credit Total au crédit
 * @property bool $valide Si l'écriture est validée
 * @property \Carbon\Carbon|null $valide_at Date de validation
 * @property int|null $valide_par ID du validateur
 * @property int|null $createur_id ID du créateur
 * @property string|null $notes Notes additionnelles
 *
 * @property-read \App\Models\Gel\Cabinet $cabinet Cabinet associé
 * @property-read \App\Models\Gel\Client|null $client Client associé
 * @property-read \App\Models\Gel\Journal $journal Journal comptable
 * @property-read \App\Models\Gel\ExerciceComptable $exercice Exercice comptable
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\LigneEcriture[] $lignes Lignes d'écriture
 * @property-read \App\Models\User|null $validePar Utilisateur validateur
 * @property-read \App\Models\User|null $createur Utilisateur créateur
 */
class EcritureComptable extends Model
{
    use SoftDeletes, ClientScopedModel;

    protected $table = 'gel_ecritures';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'journal_id',
        'exercice_id',
        'numero',
        'date_ecriture',
        'date_piece',
        'ref_piece',
        'libelle',
        'total_debit',
        'total_credit',
        'valide',
        'valide_at',
        'valide_par',
        'createur_id',
        'notes',
        // Lien retour vers l'élément d'origine (facture, dépense…) — §2.2
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'date_ecriture' => 'date',
        'date_piece' => 'date',
        'valide' => 'boolean',
        'valide_at' => 'datetime',
        'total_debit' => 'decimal:0',
        'total_credit' => 'decimal:0',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function exercice()
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_id');
    }

    public function lignes()
    {
        return $this->hasMany(LigneEcriture::class, 'ecriture_id');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function scopeValide($query)
    {
        return $query->where('valide', true);
    }

    public function scopeNonValide($query)
    {
        return $query->where('valide', false);
    }

    public function estEquilibree(): bool
    {
        return $this->total_debit === $this->total_credit;
    }
}
