<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Cabinet (Cabinet comptable).
 *
 * Représente un cabinet d'expertise comptable dans le module GEL.
 * Un cabinet regroupe des clients, des utilisateurs, des comptes comptables,
 * des journaux, des exercices et des écritures comptables.
 * Table associée : `gel_cabinets`.
 *
 * @property int $id
 * @property string $nom Nom du cabinet
 * @property string $slug Slug unique pour l'URL
 * @property string|null $email Email de contact
 * @property string|null $telephone Téléphone
 * @property string|null $adresse Adresse postale
 * @property string|null $ville Ville
 * @property string|null $ifu Numéro IFU (Identifiant Fiscal Unique)
 * @property string|null $rc Numéro RCCM
 * @property string|null $logo Chemin du logo
 * @property bool $actif Si le cabinet est actif
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\Client[] $clients Clients du cabinet
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users Utilisateurs du cabinet
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\CompteComptable[] $comptesComptables Plan comptable
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\Journal[] $journaux Journaux comptables
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\ExerciceComptable[] $exercices Exercices comptables
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\EcritureComptable[] $ecritures Écritures comptables
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\ClientInvitation[] $invitations Invitations envoyées
 */
class Cabinet extends Model
{
    protected $table = 'gel_cabinets';

    protected $fillable = [
        'nom',
        'slug',
        'email',
        'telephone',
        'adresse',
        'ville',
        'ifu',
        'rc',
        'logo',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'statut' => 'string',
        'config' => 'array',
        'limits' => 'array',
    ];

    public function portalContacts()
    {
        return $this->belongsToMany(\App\Models\PortalContact::class, 'contact_entreprise', 'client_id', 'portal_contact_id')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    // Nouveaux modèles Admin
    public function invitationsCabinet()
    {
        return $this->hasMany(\App\Models\GelAdmin\CabinetInvitation::class, 'cabinet_id');
    }

    public function subscriptionInvoices()
    {
        return $this->hasMany(\App\Models\GelAdmin\CabinetSubscriptionInvoice::class, 'cabinet_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'cabinet_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'cabinet_id');
    }

    public function comptesComptables()
    {
        return $this->hasMany(CompteComptable::class, 'cabinet_id');
    }

    public function journaux()
    {
        return $this->hasMany(Journal::class, 'cabinet_id');
    }

    public function exercices()
    {
        return $this->hasMany(ExerciceComptable::class, 'cabinet_id');
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'cabinet_id');
    }

    public function invitations()
    {
        return $this->hasMany(ClientInvitation::class, 'cabinet_id');
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
