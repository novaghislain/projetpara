<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Entreprise (Entreprise cliente).
 *
 * Représente une entreprise dans le module GEL, pouvant être associée
 * à plusieurs cabinets comptables via une relation many-to-many.
 * L'entreprise est liée à des utilisateurs propriétaires.
 * Table associée : `gel_entreprises`.
 *
 * @property int $id
 * @property string $nom Nom de l'entreprise
 * @property string|null $slug Slug unique
 * @property string|null $ifu Numéro IFU
 * @property string|null $rc Numéro RCCM
 * @property string|null $telephone Téléphone
 * @property string|null $adresse Adresse postale
 * @property string|null $ville Ville
 * @property string|null $pays Pays
 * @property string|null $secteur Secteur d'activité
 * @property string|null $email Email de contact
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $proprietaires Propriétaires de l'entreprise
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\Cabinet[] $cabinets Cabinets comptables associés
 */
class Entreprise extends Model
{
    use SoftDeletes;

    protected $table = 'gel_entreprises';

    protected $fillable = [
        'nom',
        'slug',
        'ifu',
        'rc',
        'telephone',
        'adresse',
        'ville',
        'pays',
        'secteur',
        'email',
    ];

    /**
     * Propriétaire(s) de l'entreprise (users avec entreprise_id pointant ici).
     */
    public function proprietaires()
    {
        return $this->hasMany(User::class, 'entreprise_id');
    }

    /**
     * Cabinets comptables associés à cette entreprise.
     */
    public function cabinets()
    {
        return $this->belongsToMany(Cabinet::class, 'gel_cabinet_clients', 'entreprise_id', 'cabinet_id')
            ->withPivot('statut')
            ->withTimestamps();
    }

    /**
     * Scope : entreprises actives (propriétaire non supprimé).
     */
    public function scopeActif($query)
    {
        return $query->whereExists(function ($q) {
            $q->selectRaw(1)
              ->from('users')
              ->whereColumn('users.entreprise_id', 'gel_entreprises.id')
              ->where(function ($sub) {
                  $sub->where('users.is_suspended', false)->orWhereNull('users.is_suspended');
              });
        });
    }
}
