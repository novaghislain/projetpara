<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\Auditable;

/**
 * Modèle User - Utilisateur de la plateforme.
 *
 * Table associée : 'users'.
 * Modèle central d'authentification avec support multi-entreprise,
 * permissions Spatie/Legacy, rôles, onboarding, et double facteur.
 * Relations principales :
 * - roleModel() : appartient à un rôle (Role) via 'role_id' (legacy).
 * - rolePermissions() : permissions héritées du rôle (HasManyThrough).
 * - directPermissions() / directPermissionModels() : permissions directes (UserPermission).
 * - activeClient() : client actif (Client) sélectionné en session.
 * - userClients() : toutes les entreprises rattachées (UserClient).
 * - pole() : appartient à un pôle (Pole).
 * - tenant() : appartient à un tenant (Tenant).
 * - client() : appartient à un client principal (Client).
 * - entreprise() : appartient à une entreprise GEL (Entreprise).
 * - assignedMissions(), createdMissions(), missionCollaborations() : missions.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, Auditable;
    use HasRoles {
        hasRole as spatieHasRole;
        permissions as spatiePermissions;
    }

    protected $fillable = [
        'tenant_id',
        'account_type',
        'name',
        'prenom',
        'email',
        'password',
        'role',
        'pole_id',
        'phone',
        'is_active',
        'is_admin',
        'client_id',
        'active_client_id',
        'is_company_admin',
        'role_id',
        'fonction',
        'photo',
        'role_secretaire',
        'clients_assignes',
        'onboarding_token',
        'onboarding_completed',
        'wants_accounting',
        'wants_secretary',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'last_login_at',
        'last_login_ip',
        'login_count',
        'is_suspended',
        'suspended_at',
        'suspended_reason',
        'must_change_password',
        'cabinet_id',
        'entreprise_id',
        'workspace_type',
        'account_context',
        'active_account_context',
        'trial_ends_at',
        'subscription_status',
        'plan_id',
        'personal_company_name',
        'personal_industry',
        'pool_max_capacity',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'is_company_admin' => 'boolean',
            'role_secretaire' => 'boolean',
            'clients_assignes' => 'array',
            'is_suspended' => 'boolean',
            'must_change_password' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'suspended_at' => 'datetime',
            'last_login_at' => 'datetime',
            'login_count' => 'integer',
            'onboarding_completed' => 'boolean',
            'wants_accounting' => 'boolean',
            'wants_secretary' => 'boolean',
            'trial_ends_at' => 'datetime',
            'account_context' => 'array',
        ];
    }

    // ─── Relations Rôle / Permission (Legacy) ──────────────────────────

    /**
     * Le rôle principal de l'utilisateur (FK vers roles legacy).
     */
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Toutes les permissions de l'utilisateur, via son rôle legacy.
     */
    public function rolePermissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            Role::class,
            'id',           // roles.id
            'id',           // permissions.id → role_permission.permission_id
            'role_id',      // users.role_id
            'id'            // roles.id → role_permission.role_id
        );
    }

    /**
     * Permissions directes attribuées à l'utilisateur (via user_permissions).
     */
    public function directPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Permissions directes (modèle Permission) via la table pivot user_permissions.
     */
    public function directPermissionModels()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->select('permissions.*')
            ->withPivot('granted_by', 'granted_at', 'client_id', 'expires_at')
            ->withTimestamps();
    }

    /**
     * Récupère TOUTES les permissions effectives (rôle + directes).
     */
    public function effectivePermissions()
    {
        $directPerms = $this->directPermissionModels()->get()->keyBy('id');

        if ($directPerms->isNotEmpty()) {
            return $directPerms;
        }

        return $this->roleModel
            ? $this->roleModel->permissions()->get()->keyBy('id')
            : collect();
    }

    // ─── Relations Multi-Tenant ──────────────────────────────────────────

    /**
     * Client actif sélectionné par l'utilisateur (multi-entreprise).
     */
    public function activeClient()
    {
        return $this->belongsTo(Client::class, 'active_client_id');
    }

    /**
     * Toutes les entreprises auxquelles l'utilisateur est rattaché.
     */
    public function userClients()
    {
        return $this->hasMany(UserClient::class);
    }

    /**
     * Récupère uniquement les entreprises actives de l'utilisateur.
     */
    public function activeUserClients()
    {
        return $this->userClients()->where('is_active', true);
    }

    // ─── Vérifications de rôle (Spatie + Legacy) ────────────────────────

    /**
     * Vérifie si l'utilisateur a un rôle spécifique.
     * Délègue à Spatie HasRoles par défaut.
     */
    public function hasRole($roles, string $guard = null): bool
    {
        return $this->spatieHasRole($roles, $guard);
    }

    /**
     * Vérifie le legacy role string de l'utilisateur.
     */
    public function hasRoleName(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Vérifie si l'utilisateur a au moins le rôle donné (hiérarchie legacy).
     */
    public function hasMinRole(string $role): bool
    {
        $hierarchy = ['collaborator' => 0, 'pole_responsible' => 1, 'director' => 2, 'super_admin' => 3];
        $userLevel = $hierarchy[$this->role] ?? -1;
        $requiredLevel = $hierarchy[$role] ?? 0;
        return $userLevel >= $requiredLevel;
    }

    /**
     * Vérifie si l'utilisateur est Super Admin (via Spatie ou legacy).
     */
    public function isSuperAdmin(): bool
    {
        if ($this->role === 'super_admin') return true;
        if ($this->roleModel && $this->roleModel->slug === 'super_admin') return true;
        return $this->hasRole('super_admin');
    }

    /**
     * Vérifie si l'utilisateur est Administrateur entreprise.
     */
    public function isCompanyAdmin(): bool
    {
        if ($this->is_company_admin) return true;
        if ($this->hasRole('admin')) return true;
        return false;
    }

    /**
     * Vérifie si l'utilisateur est un Consultant.
     */
    public function isConsultant(): bool
    {
        return $this->account_type === 'consultant' || $this->role === 'consultant';
    }

    // ─── Relations Consultant ──────────────────────────────────────────

    public function consultantMissions()
    {
        return $this->hasMany(\App\Models\Gel\ConsultantMission::class, 'consultant_id');
    }

    public function consultantDeliverables()
    {
        return $this->hasMany(\App\Models\Gel\ConsultantDeliverable::class, 'consultant_id');
    }

    public function consultantAudits()
    {
        return $this->hasMany(\App\Models\Gel\ConsultantAudit::class, 'user_id');
    }

    /**
     * Vérifie si l'utilisateur est un comptable du cabinet.
     */
    public function isComptable(): bool
    {
        if ($this->role === 'comptable') {
            return is_null($this->client_id) || is_null($this->entreprise_id);
        }
        if ($this->roleModel && $this->roleModel->slug === 'comptable') {
            return is_null($this->client_id);
        }
        if ($this->hasRole(['comptable_senior', 'chef_comptable', 'comptable_junior'])) return true;
        return false;
    }

    /**
     * Vérifie si l'utilisateur est un client (rôle client).
     */
    public function isClient(): bool
    {
        if ($this->roleModel && $this->roleModel->slug === 'client') return true;
        if ($this->role === 'client') return true;
        return false;
    }

    /**
     * Vérifie si l'utilisateur est un secrétaire.
     */
    public function isSecretaire(): bool
    {
        if ($this->role === 'secretaire' || $this->role === 'secretary' || $this->role_secretaire) return true;
        if ($this->roleModel && $this->roleModel->slug === 'secretaire') return true;
        if ($this->hasRole('secretaire') || $this->hasRole('secretary')) return true;
        return false;
    }

    /**
     * Vérifie si l'utilisateur est un manager d'entreprise.
     */
    public function isCompanyManager(): bool
    {
        if ($this->roleModel && $this->roleModel->slug === 'company_manager') return true;
        if ($this->hasRole('entreprise_admin')) return true;
        return false;
    }

    /**
     * Vérifie si l'utilisateur appartient au Pôle Communication Digitale (Modèle 3).
     */
    public function isCommunication(): bool
    {
        if ($this->account_type === 'communication') return true;
        if ($this->role === 'communication') return true;
        if ($this->hasRole('communication')) return true;
        
        // L'informaticien hérite des accès communication (fusion IT / Média)
        if ($this->account_type === 'informaticien' || $this->role === 'informaticien') return true;
        
        return false;
    }

    /**
     * Vérifie si l'utilisateur a plusieurs entreprises.
     */
    public function hasMultipleCompanies(): bool
    {
        return $this->activeUserClients()->count() > 1;
    }

    /**
     * Vérifie si l'utilisateur est suspendu.
     */
    public function isSuspended(): bool
    {
        return $this->is_suspended ?? false;
    }

    /**
     * Vérifie si c'est un secrétaire indépendant (autonome). [Modèle 3A]
     */
    public function isAutonomousSecretary(): bool
    {
        return $this->workspace_type === 'individuel';
    }

    /**
     * Vérifie si c'est un comptable indépendant (autonome). [Modèle 3B]
     */
    public function isAutonomousAccountant(): bool
    {
        return $this->workspace_type === 'individuel_comptable';
    }

    /**
     * Vérifie si c'est un personnel du pool GEL SABINET. [Modèle 2]
     */
    public function isGelPoolStaff(): bool
    {
        return $this->workspace_type === 'gel_pool';
    }

    /**
     * Retourne vrai si l'utilisateur a plusieurs contextes de travail actifs.
     * Ex : comptable invité par une entreprise (Modèle 1) ET indépendant (Modèle 3).
     */
    public function hasMultipleContexts(): bool
    {
        $contexts = $this->account_context ?? [];
        return count($contexts) > 1;
    }

    /**
     * Retourne la liste des contextes disponibles pour cet utilisateur.
     * Structure : [['key' => 'model1', 'label' => 'Votre entreprise'], ...]
     */
    public function getAvailableContexts(): array
    {
        $contexts = $this->account_context ?? [];
        $labels = [
            'model1'             => 'Accès Entreprise (invité)',
            'model2_gel_pool'    => 'Personnel GEL SABINET',
            'model3_secretaire'  => 'Espace Secrétariat Indépendant',
            'model3_comptable'   => 'Espace Comptable Indépendant',
        ];
        return array_map(fn($key) => ['key' => $key, 'label' => $labels[$key] ?? $key], $contexts);
    }

    /**
     * Vérifie si l'abonnement (secrétaire ou comptable individuel) est actif ou en essai valide.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->subscription_status === 'active') return true;
        if ($this->subscription_status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture()) return true;
        return false;
    }

    // ─── Vérifications de permissions ───────────────────────────────────

    /**
     * Vérifie si l'utilisateur a accès à un module.
     */
    public function hasModule(string $module): bool
    {
        // Super Admin a toujours accès
        if ($this->isSuperAdmin()) return true;

        // Admin entreprise a accès si le module est activé pour son entreprise
        if ($this->isCompanyAdmin()) {
            return $this->isModuleEnabledForClient($module);
        }

        // Vérifier via Spatie
        $modulePerms = \Spatie\Permission\Models\Permission::where('module', $module)->pluck('name')->toArray();
        if ($this->hasAnyPermission($modulePerms)) {
            return $this->isModuleEnabledForClient($module);
        }

        // Si l'utilisateur possède des permissions directes, elles surchargent le rôle
        $hasDirect = $this->directPermissionModels()->exists();
        if ($hasDirect) {
            return $this->directPermissionModels()
                ->where('module', $module)
                ->exists() && $this->isModuleEnabledForClient($module);
        }

        // Sinon, vérifier dans les permissions du rôle legacy
        if ($this->roleModel && $this->roleModel->hasModule($module)) {
            return $this->isModuleEnabledForClient($module);
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique (module.action).
     */
    public function canModule(string $module, string $action): bool
    {
        // Super Admin a toutes les permissions
        if ($this->isSuperAdmin()) return true;

        // Admin entreprise a accès à tout ce qui concerne son entreprise
        if ($this->isCompanyAdmin()) return true;

        // Vérifier via Spatie
        $permName = $module . '.' . $action;
        if ($this->hasPermissionTo($permName)) return true;

        // Si l'utilisateur possède des permissions directes, elles surchargent le rôle
        $hasDirect = $this->directPermissionModels()->exists();
        if ($hasDirect) {
            return $this->directPermissionModels()
                ->where('module', $module)
                ->where('action', $action)
                ->exists();
        }

        // Sinon, vérifier dans les permissions du rôle legacy
        if ($this->roleModel && $this->roleModel->hasPermission($module, $action)) {
            return true;
        }

        return false;
    }

    /**
     * Alias pour vérifier une permission (module:action).
     */
    public function hasModuleAction(string $module, string $action): bool
    {
        return $this->canModule($module, $action);
    }

    /**
     * Récupère la liste des modules auxquels l'utilisateur a accès.
     */
    public function getAccessibleModules(): array
    {
        if ($this->isSuperAdmin()) {
            return \Spatie\Permission\Models\Permission::distinct()->pluck('module')->toArray();
        }

        if ($this->isCompanyAdmin()) {
            $allModules = \Spatie\Permission\Models\Permission::distinct()->pluck('module')->toArray();
            if ($this->active_client_id) {
                $client = Client::find($this->active_client_id);
                $disabled = $client ? ($client->disabled_modules ?? []) : [];
                return array_values(array_diff($allModules, $disabled));
            }
            return $allModules;
        }

        // Via Spatie
        $modules = \Spatie\Permission\Models\Permission::whereIn('name', $this->getAllPermissions()->pluck('name'))
            ->distinct()
            ->pluck('module')
            ->toArray();

        if (empty($modules)) {
            // Fallback legacy
            $hasDirect = $this->directPermissionModels()->exists();
            if ($hasDirect) {
                $modules = $this->directPermissionModels()
                    ->distinct()
                    ->pluck('module')
                    ->toArray();
            } else {
                $modules = $this->roleModel
                    ? $this->roleModel->permissions()->distinct()->pluck('module')->toArray()
                    : [];
            }
        }

        // Filtrer par modules activés client
        return array_values(array_filter($modules, fn($mod) => $this->isModuleEnabledForClient($mod)));
    }

    /**
     * Vérifie si l'utilisateur a accès à un module spécifique (ancien nom).
     */
    public function hasModuleAccess(string $moduleSlug): bool
    {
        return $this->hasModule($moduleSlug);
    }

    /**
     * Vérifie si un module est activé pour le client actif.
     */
    public function isModuleEnabledForClient(string $module): bool
    {
        $clientId = $this->active_client_id ?? $this->client_id;
        if (!$clientId) return true; // Pas de contexte client → pas de restriction

        $client = Client::find($clientId);
        if (!$client) return true;

        $disabled = $client->disabled_modules ?? [];
        if (in_array($module, $disabled)) return false;

        // Vérifier dans client_modules si la table existe
        if (\Illuminate\Support\Facades\Schema::hasTable('client_modules')) {
            $cm = ClientModule::where('client_id', $clientId)
                ->where('module', $module)
                ->first();
            if ($cm && !$cm->is_active) return false;
        }

        return true;
    }

    /**
     * Récupère les champs cachés pour un module donné.
     */
    public function hiddenFields(string $module): array
    {
        $roleSlug = $this->roleModel?->slug;

        $restrictions = PermissionFieldRestriction::where('module', $module)
            ->where('is_active', true)
            ->where(function ($q) use ($roleSlug) {
                $q->whereNull('role_slug')
                  ->orWhere('role_slug', $roleSlug);
            })
            ->get();

        $hidden = [];
        foreach ($restrictions as $r) {
            $fields = $r->hidden_fields ?? [];
            $hidden = array_merge($hidden, $fields);
        }

        return array_unique($hidden);
    }

    /**
     * Récupère les IDs des permissions directes.
     */
    public function getDirectPermissionIds(): array
    {
        return $this->directPermissionModels()->pluck('permissions.id')->toArray();
    }

    /**
     * Récupère les permissions formatées pour le frontend (module.action).
     */
    public function getFormattedPermissions(): array
    {
        if ($this->isSuperAdmin()) {
            return \Spatie\Permission\Models\Permission::all()
                ->map(fn($p) => $p->name)
                ->toArray();
        }

        if ($this->isCompanyAdmin()) {
            $modules = $this->getAccessibleModules();
            return \Spatie\Permission\Models\Permission::whereIn('module', $modules)
                ->get()
                ->map(fn($p) => $p->name)
                ->toArray();
        }

        return $this->getAllPermissions()
            ->map(fn($p) => $p->name)
            ->values()
            ->toArray();
    }

    // ─── Contexte entreprise ────────────────────────────────────────────

    /**
     * Basculer le contexte d'entreprise actif.
     */
    public function switchToClient(int $clientId): bool
    {
        // Super-admins, comptables et secrétaires peuvent basculer sur n'importe quel client
        if ($this->isSuperAdmin() || $this->isComptable() || $this->isSecretaire()) {
            $client = \App\Models\Client::find($clientId);
            if (!$client) return false;

            $this->active_client_id = $clientId;
            $this->save();

            // Créer l'entrée user_client si elle n'existe pas
            \App\Models\UserClient::firstOrCreate(
                ['user_id' => $this->id, 'client_id' => $clientId],
                ['is_active' => true]
            );

            return true;
        }

        $exists = ($clientId === (int)$this->client_id) || $this->activeUserClients()
            ->where('client_id', $clientId)
            ->exists();

        if (!$exists) return false;

        $this->active_client_id = $clientId;
        $this->save();

        // Mettre à jour last_accessed_at
        UserClient::where('user_id', $this->id)
            ->where('client_id', $clientId)
            ->update(['last_accessed_at' => now()]);

        return true;
    }

    // ─── Login tracking ─────────────────────────────────────────────────

    /**
     * Enregistrer la connexion.
     */
    public function recordLogin(string $ip): void
    {
        $this->last_login_at = now();
        $this->last_login_ip = $ip;
        $this->login_count = ($this->login_count ?? 0) + 1;
        $this->save();
    }

    /**
     * Forcer le changement de mot de passe.
     */
    public function forcePasswordChange(): void
    {
        $this->must_change_password = true;
        $this->save();
    }

    // ─── Accessors ──────────────────────────────────────────────────────

    /**
     * Accessor pour is_super_admin (utilisé par le frontend).
     */
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->isSuperAdmin();
    }

    // ─── Scopes ─────────────────────────────────────────────────────────

    /**
     * Scope: utilisateurs actifs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: utilisateurs non suspendus.
     */
    public function scopeNotSuspended($query)
    {
        return $query->where(function ($q) {
            $q->where('is_suspended', false)->orWhereNull('is_suspended');
        });
    }

    /**
     * Scope: utilisateurs appartenant à une entreprise.
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope: utilisateurs avec un rôle spécifique.
     */
    public function scopeByRoleSlug($query, string $slug)
    {
        return $query->whereHas('roleModel', fn($q) => $q->where('slug', $slug));
    }

    // ─── Relations GEL / Onboarding ─────────────────────────────────────

    /**
     * Entreprise (pour les propriétaires d'entreprise).
     */
    public function entreprise()
    {
        return $this->belongsTo(\App\Models\Gel\Entreprise::class, 'entreprise_id');
    }

    /**
     * Vérifie si l'utilisateur est propriétaire d'une entreprise.
     */
    public function estProprietaireEntreprise(): bool
    {
        return $this->account_type === 'entreprise' && !is_null($this->entreprise_id);
    }

    /**
     * Vérifie si l'utilisateur a complété son onboarding.
     * Retourne true pour les utilisateurs legacy (account_type = client|internal|super_admin).
     */
    public function hasCompletedOnboarding(): bool
    {
        // Legacy : ancien type de compte → pas d'onboarding GEL nécessaire
        if (in_array($this->account_type, ['client', 'internal', 'super_admin', null])) {
            return true;
        }

        // Le flag onboarding_completed est la source de vérité.
        // Les fallbacks entreprise_id/cabinet_id ne s'appliquent que si le flag
        // n'a jamais été renseigné (comptes legacy) — jamais quand il est
        // explicitement false (entreprise réinitialisée via onboarding:reset).
        return $this->onboarding_completed === true
            || ($this->onboarding_completed === null
                && (($this->account_type === 'entreprise' && $this->entreprise_id)
                    || ($this->account_type === 'cabinet' && $this->cabinet_id)));
    }

    /**
     * Vérifie si l'utilisateur est un comptable/expert-comptable (compte cabinet).
     */
    public function isAccountant(): bool
    {
        return $this->account_type === 'cabinet' || !empty($this->cabinet_id);
    }

    // ─── Relations existantes ───────────────────────────────────────────

    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }

    public function assignedMissions()
    {
        return $this->hasMany(Mission::class, 'assigned_to');
    }

    public function createdMissions()
    {
        return $this->hasMany(Mission::class, 'created_by');
    }

    public function missionCollaborations()
    {
        return $this->belongsToMany(Mission::class, 'mission_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function createdClients()
    {
        return $this->hasMany(Client::class, 'created_by');
    }

    public function uploadedDocuments()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->whereNull('read_at');
    }

    /**
     * Les missions IT affectées à cet informaticien
     */
    public function itMissions()
    {
        return $this->belongsToMany(\App\Models\Gel\ItMission::class, 'it_mission_user', 'user_id', 'it_mission_id');
    }
}
