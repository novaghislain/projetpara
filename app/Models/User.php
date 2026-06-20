<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, \App\Traits\Auditable;

    protected $fillable = [
        'name',
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
        ];
    }

    // ─── Relations Rôle / Permission ─────────────────────────────────────

    /**
     * Le rôle principal de l'utilisateur (FK vers roles).
     */
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Toutes les permissions de l'utilisateur, via son rôle.
     */
    public function permissions()
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
            ->withPivot('granted_by', 'granted_at', 'client_id', 'expires_at')
            ->withTimestamps();
    }

    /**
     * Récupère TOUTES les permissions effectives (rôle + directes).
     */
    public function effectivePermissions()
    {
        $rolePerms = $this->roleModel
            ? $this->roleModel->permissions()->get()->keyBy('id')
            : collect();

        $directPerms = $this->directPermissionModels()->get()->keyBy('id');

        return $rolePerms->merge($directPerms);
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

    // ─── Vérifications de rôle ──────────────────────────────────────────

    /**
     * Vérifie si l'utilisateur a un rôle spécifique (string).
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Vérifie si l'utilisateur a au moins le rôle donné (hiérarchie).
     */
    public function hasMinRole(string $role): bool
    {
        $hierarchy = ['collaborator' => 0, 'pole_responsible' => 1, 'director' => 2, 'super_admin' => 3];
        $userLevel = $hierarchy[$this->role] ?? -1;
        $requiredLevel = $hierarchy[$role] ?? 0;
        return $userLevel >= $requiredLevel;
    }

    /**
     * Vérifie si l'utilisateur est Super Admin (via le système de rôles ou l'ancien champ).
     */
    public function isSuperAdmin(): bool
    {
        if ($this->role === 'super_admin') return true;
        if ($this->roleModel && $this->roleModel->slug === 'super_admin') return true;
        return false;
    }

    /**
     * Vérifie si l'utilisateur est Administrateur entreprise.
     */
    public function isCompanyAdmin(): bool
    {
        if ($this->is_company_admin) return true;
        if ($this->roleModel && $this->roleModel->slug === 'company_admin') return true;
        return false;
    }

    /**
     * Vérifie si l'utilisateur est un comptable du cabinet.
     */
    public function isComptable(): bool
    {
        if ($this->roleModel && $this->roleModel->slug === 'comptable') return true;
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
     * Vérifie si l'utilisateur est un manager d'entreprise.
     */
    public function isCompanyManager(): bool
    {
        if ($this->roleModel && $this->roleModel->slug === 'company_manager') return true;
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

        // Vérifier dans les permissions du rôle
        if ($this->roleModel && $this->roleModel->hasModule($module)) {
            return $this->isModuleEnabledForClient($module);
        }

        // Vérifier dans les permissions directes
        return $this->directPermissionModels()
            ->where('module', $module)
            ->exists();
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique (module + action).
     */
    public function canModule(string $module, string $action): bool
    {
        // Super Admin a toutes les permissions
        if ($this->isSuperAdmin()) return true;

        // Admin entreprise a accès à tout ce qui concerne son entreprise
        if ($this->isCompanyAdmin()) return true;

        // Vérifier dans les permissions du rôle
        if ($this->roleModel && $this->roleModel->hasPermission($module, $action)) {
            return true;
        }

        // Vérifier dans les permissions directes
        return $this->directPermissionModels()
            ->where('module', $module)
            ->where('action', $action)
            ->exists();
    }

    /**
     * Alias pour vérifier une permission (module:action).
     * NOTE: ne s'appelle pas « can » car Laravel\Authorizable::can($abilities, $arguments = [])
     * aurait un conflit de signature.
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
            return Permission::distinct()->pluck('module')->toArray();
        }

        if ($this->isCompanyAdmin()) {
            $allModules = Permission::distinct()->pluck('module')->toArray();
            if ($this->active_client_id) {
                $client = Client::find($this->active_client_id);
                $disabled = $client ? ($client->disabled_modules ?? []) : [];
                return array_values(array_diff($allModules, $disabled));
            }
            return $allModules;
        }

        // Modules via le rôle
        $roleModules = $this->roleModel
            ? $this->roleModel->permissions()->distinct()->pluck('module')->toArray()
            : [];

        // Modules via les permissions directes
        $directModules = $this->directPermissionModels()
            ->distinct()
            ->pluck('module')
            ->toArray();

        // Fusionner et filtrer par modules activés client
        $merged = array_values(array_unique(array_merge($roleModules, $directModules)));
        return array_values(array_filter($merged, fn($mod) => $this->isModuleEnabledForClient($mod)));
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
     * Récupère les permissions formatées pour le frontend (module:action).
     */
    public function getFormattedPermissions(): array
    {
        if ($this->isSuperAdmin()) {
            return Permission::all()
                ->map(fn($p) => $p->module . ':' . $p->action)
                ->toArray();
        }

        if ($this->isCompanyAdmin()) {
            $modules = $this->getAccessibleModules();
            return Permission::whereIn('module', $modules)
                ->get()
                ->map(fn($p) => $p->module . ':' . $p->action)
                ->toArray();
        }

        return $this->effectivePermissions()
            ->map(fn($p) => $p->module . ':' . $p->action)
            ->values()
            ->toArray();
    }

    // ─── Contexte entreprise ────────────────────────────────────────────

    /**
     * Basculer le contexte d'entreprise actif.
     */
    public function switchToClient(int $clientId): bool
    {
        $exists = $this->activeUserClients()
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
}
