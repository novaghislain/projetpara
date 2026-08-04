<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\ClientScopedModel;

/**
 * Modèle ExerciceComptable (Exercice comptable - espace Gel).
 *
 * Définit un exercice comptable avec sa période (date_debut, date_fin).
 * Permet de suivre l'état de clôture et l'utilisateur ayant effectué la clôture.
 * Table associée : `gel_exercices`.
 * Supporte la suppression douce (SoftDeletes).
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property int|null $client_id ID du client
 * @property string $libelle Libellé de l'exercice
 * @property \Carbon\Carbon $date_debut Date de début
 * @property \Carbon\Carbon $date_fin Date de fin
 * @property bool $cloture Si l'exercice est clôturé
 * @property \Carbon\Carbon|null $cloture_at Date de clôture
 * @property int|null $cloture_par ID de l'utilisateur ayant clôturé
 *
 * @property-read \App\Models\Gel\Cabinet $cabinet Cabinet associé
 * @property-read \App\Models\Gel\Client|null $client Client associé
 * @property-read \App\Models\User|null $cloturePar Utilisateur ayant clôturé
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\EcritureComptable[] $ecritures Écritures de l'exercice
 */
class ExerciceComptable extends Model
{
    use SoftDeletes, ClientScopedModel;

    protected $table = 'gel_exercices';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'libelle',
        'date_debut',
        'date_fin',
        'cloture',
        'cloture_at',
        'cloture_par',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'cloture' => 'boolean',
        'cloture_at' => 'datetime',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function cloturePar()
    {
        return $this->belongsTo(User::class, 'cloture_par');
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'exercice_id');
    }

    public function scopeNonCloture($query)
    {
        return $query->where('cloture', false);
    }

    public function scopeEnCours($query)
    {
        return $query->where('cloture', false)
            ->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now());
    }
}
