<?php

namespace App\Models;

use App\Models\Devis;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une entreprise cliente du cabinet.
 *
 * C'est l'entité centrale de l'application. Chaque client est
 * une entreprise qui utilise les services du cabinet comptable.
 * Il possède un domaine d'activité, des modules comptables activés,
 * des contacts, des dossiers, des documents, des missions et des
 * licences. Inclut la gestion des aspects fiscaux (IFU, RCCM,
 * régime fiscal, e-MECEf) et de sécurité (2FA, IPs autorisées).
 *
 * @property int $id
 * @property string $company_name Raison sociale
 * @property string|null $legal_form Forme juridique
 * @property string|null $rccm RCCM
 * @property string|null $ifu IFU (Identifiant Fiscal Unique)
 * @property string|null $address Adresse
 * @property string|null $city Ville
 * @property string|null $secteur Secteur d'activité
 * @property int|null $score Score de lead (0-100)
 * @property string|null $country Pays
 * @property string|null $phone Téléphone
 * @property string|null $email Email
 * @property string|null $website Site web
 * @property string $status Statut (actif, inactif, prospect, suspendu)
 * @property string|null $contract_type Type de contrat
 * @property string|null $contract_start Date de début de contrat
 * @property string|null $contract_end Date de fin de contrat
 * @property string|null $notes Notes
 * @property array|null $disabled_modules Modules désactivés
 * @property bool $require_2fa Authentification à deux facteurs requise
 * @property int $session_timeout_minutes Délai d'expiration de session
 * @property array|null $allowed_ips Adresses IP autorisées
 * @property string|null $regime_fiscal Régime fiscal
 * @property string|null $emecef_nim NIM e-MECEf
 * @property bool $emecef_is_active e-MECEf actif
 * @property string|null $emecef_password Mot de passe e-MECEf (chiffré)
 * @property int|null $created_by Identifiant du créateur
 * @property string|null $domain_code Code du domaine
 * @property int|null $domain_id Identifiant du domaine d'activité
 * @property bool $domain_confirmed Domaine confirmé
 * @property string|null $domain_confirmed_at Date de confirmation du domaine
 *
 * @property-read BusinessDomain|null $domain Domaine d'activité
 * @property-read \Illuminate\Database\Eloquent\Collection|ClientAccountingModule[] $accountingModules Modules comptables
 * @property-read \Illuminate\Database\Eloquent\Collection|ClientAccountingModule[] $activeAccountingModules Modules comptables actifs
 * @property-read \Illuminate\Database\Eloquent\Collection|ClientContact[] $contacts Contacts
 * @property-read ClientContact|null $primaryContact Contact principal
 * @property-read \Illuminate\Database\Eloquent\Collection|Pole[] $poles Pôles d'activité
 * @property-read \Illuminate\Database\Eloquent\Collection|Pole[] $activePoles Pôles actifs
 * @property-read \Illuminate\Database\Eloquent\Collection|Mission[] $missions Missions
 * @property-read \Illuminate\Database\Eloquent\Collection|Service[] $services Services souscrits
 * @property-read \Illuminate\Database\Eloquent\Collection|ClientFolder[] $folders Dossiers
 * @property-read \Illuminate\Database\Eloquent\Collection|Document[] $documents Documents
 * @property-read User|null $createdBy Créateur
 * @property-read \Illuminate\Database\Eloquent\Collection|License[] $licenses Licences
 * @property-read \Illuminate\Database\Eloquent\Collection|License[] $activeLicenses Licences actives
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $companyAdmins Administrateurs
 * @property-read \Illuminate\Database\Eloquent\Collection|Devis[] $devis Devis
 *
 * @table clients
 */
class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'legal_form',
        'rccm',
        'ifu',
        'address',
        'city',
        'secteur',
        'score',
        'country',
        'phone',
        'email',
        'website',
        'status',
        'contract_type',
        'contract_start',
        'contract_end',
        'notes',
        'disabled_modules',
        'require_2fa',
        'session_timeout_minutes',
        'allowed_ips',
        'regime_fiscal',
        'emecef_nim',
        'emecef_is_active',
        'emecef_password',
        'created_by',
        'domain_code',
        'domain_id',
        'domain_confirmed',
        'domain_confirmed_at',
        'wants_accounting',
        'wants_secretary',
        'portal_slug',
        'portal_active',
    ];

    protected function casts(): array
    {
        return [
            'contract_start' => 'date',
            'contract_end' => 'date',
            'disabled_modules' => 'array',
            'require_2fa' => 'boolean',
            'emecef_is_active' => 'boolean',
            'emecef_password' => 'encrypted',
            'allowed_ips' => 'json',
            'domain_confirmed_at' => 'datetime',
            'wants_accounting' => 'boolean',
            'wants_secretary' => 'boolean',
            'portal_active' => 'boolean',
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (empty($client->portal_slug) && !empty($client->company_name)) {
                $client->portal_slug = $client->generateUniqueSlug($client->company_name);
            }
        });
    }

    public function generateUniqueSlug(string $name): string
    {
        $baseSlug = \Illuminate\Support\Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (static::where('portal_slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function domain()
    {
        return $this->belongsTo(BusinessDomain::class, 'domain_id');
    }

    public function accountingModules()
    {
        return $this->hasMany(ClientAccountingModule::class, 'client_id');
    }

    public function activeAccountingModules()
    {
        return $this->hasMany(ClientAccountingModule::class, 'client_id')->where('is_active', true);
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_primary', true);
    }

    public function poles()
    {
        return $this->belongsToMany(Pole::class, 'client_pole')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function activePoles()
    {
        return $this->belongsToMany(Pole::class, 'client_pole')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    public function missions()
    {
        return $this->hasMany(Mission::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'client_service')
            ->withPivot(['status', 'start_date', 'end_date', 'settings'])
            ->withTimestamps();
    }

    public function folders()
    {
        return $this->hasMany(ClientFolder::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function activeLicenses()
    {
        return $this->hasMany(License::class)->where('status', 'active');
    }

    public function companyAdmins()
    {
        return $this->hasMany(User::class)->where('is_company_admin', true);
    }

    public function devis(): HasMany
    {
        return $this->hasMany(Devis::class);
    }

    public function scopeActif($query)
    {
        return $query->where('status', 'actif');
    }

    public function getActiveModulesAttribute(): array
    {
        $domainModules = $this->domain?->getAllModules() ?? [];
        $disabled = $this->disabled_modules ?? [];
        return array_values(array_diff($domainModules, $disabled));
    }

    /**
     * Relation : écritures comptables GEL liées à ce client.
     */
    public function gelEcritures()
    {
        return $this->hasMany(\App\Models\Gel\Comptabilite\EcritureComptable::class, 'client_id');
    }

    /**
     * Relation : exercices comptables GEL liés à ce client.
     */
    public function gelExercices()
    {
        return $this->hasMany(\App\Models\Gel\Comptabilite\ExerciceComptable::class, 'client_id');
    }

    public function portalContacts()
    {
        return $this->belongsToMany(PortalContact::class, 'contact_entreprise')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
