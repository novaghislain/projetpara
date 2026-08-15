<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Modèle Client (Client du cabinet).
 *
 * LEXIQUE TECHNIQUE : FRONTIÈRE D'ISOLATION
 * - Entreprise : Entité racine (SaaS), facturation de la plateforme GEL.
 * - Cabinet : Le cabinet d'expertise comptable (Tenant principal).
 * - Client : Le "Dossier" géré par le cabinet. Sert de base au cloisonnement 
 *            (multi-tenant métier via `client_id`) pour toutes les tables transactionnelles.
 *
 * Représente une entreprise cliente d'un cabinet comptable.
 * Un client peut avoir ses propres utilisateurs, journaux, exercices
 * et écritures comptables. Supporte la suppression douce (SoftDeletes).
 * Table associée : `gel_clients`.
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet propriétaire
 * @property string $nom_entreprise Nom de l'entreprise cliente
 * @property string|null $sigle Sigle ou abréviation
 * @property string|null $email Email de contact
 * @property string|null $telephone Téléphone
 * @property string|null $adresse Adresse postale
 * @property string|null $ville Ville
 * @property string|null $ifu Numéro IFU (Identifiant Fiscal Unique)
 * @property string|null $rc Numéro RCCM
 * @property string|null $secteur Secteur d'activité
 * @property string|null $logo Chemin du logo
 * @property string $statut Statut du client (actif, inactif, etc.)
 *
 * @property-read \App\Models\Gel\Cabinet $cabinet Cabinet propriétaire
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users Utilisateurs du client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\ClientInvitation[] $invitations Invitations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\Journal[] $journaux Journaux comptables
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\ExerciceComptable[] $exercices Exercices comptables
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\EcritureComptable[] $ecritures Écritures comptables
 */
class Client extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'gel_clients';

    protected $fillable = [
        'cabinet_id',
        'independant_client_id',
        'nom_entreprise',
        'sigle',
        'email',
        'telephone',
        'adresse',
        'ville',
        'ifu',
        'rc',
        'secteur',
        'logo',
        'statut',
        'compte_comptable_id',
        'score_conformite',
        'score_calcule_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'statut'           => 'string',
        'score_conformite' => 'integer',
        'score_calcule_at' => 'datetime',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'client_id');
    }

    public function invitations()
    {
        return $this->hasMany(ClientInvitation::class, 'client_id');
    }

    public function journaux()
    {
        return $this->hasMany(Journal::class, 'client_id');
    }

    public function exercices()
    {
        return $this->hasMany(ExerciceComptable::class, 'client_id');
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'client_id');
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function portalContacts()
    {
        return $this->belongsToMany(\App\Models\PortalContact::class, 'contact_entreprise', 'client_id', 'portal_contact_id')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function conformite()
    {
        return $this->hasMany(Conformite::class, 'client_id');
    }

    public function conformite_actions()
    {
        return $this->hasMany(ConformiteAction::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salarie::class);
    }

    public function immobilisations()
    {
        return $this->hasMany(Immobilisation::class);
    }

    public function bulletins()
    {
        return $this->hasMany(Bulletin::class);
    }

    /**
     * Obtenir les déclarations fiscales du client.
     */
    public function declarationsFiscales()
    {
        return $this->hasMany(DeclarationFiscale::class, 'client_id');
    }
}
