<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        'is_company_admin',
        'role_id',
        'fonction',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
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
     * Vérifie si l'utilisateur a une permission spécifique.
     */
    public function canModule(string $module, string $action): bool
    {
        // Super Admin a toutes les permissions
        if ($this->isSuperAdmin()) return true;

        // Admin entreprise a accès à tout ce qui concerne son entreprise
        if ($this->isCompanyAdmin()) return true;

        // Utilisateur normal : vérifier via son rôle
        if ($this->roleModel) {
            return $this->roleModel->hasPermission($module, $action);
        }

        return false;
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
            // Admin entreprise : accès aux modules sous licence de son entreprise
            if ($this->client_id) {
                return License::where('client_id', $this->client_id)
                    ->where('status', 'active')
                    ->with('service')
                    ->get()
                    ->pluck('service.slug')
                    ->toArray();
            }
            return [];
        }

        // Utilisateur normal : modules accessibles via son rôle
        if ($this->roleModel) {
            return $this->roleModel->permissions()
                ->distinct()
                ->pluck('module')
                ->toArray();
        }

        return [];
    }

    /**
     * Vérifie si l'utilisateur a accès à un module spécifique.
     */
    public function hasModuleAccess(string $moduleSlug): bool
    {
        return in_array($moduleSlug, $this->getAccessibleModules(), true);
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
