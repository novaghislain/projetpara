<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasUuids, Notifiable;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'email',
        'mot_de_passe_hash',
        'password',
        'nom',
        'telephone',
        'mfa_active',
        'mfa_secret',
        'statut',
        'cree_le',
        'supprime_le',
        'cabinet_id',
        'role',
        'active_client_id',
        'is_autonomous',
    ];

    protected $hidden = [
        'mot_de_passe_hash',
        'password',
        'remember_token',
        'mfa_secret',
    ];

    const CREATED_AT = 'cree_le';
    const UPDATED_AT = null; // Pas de updated_at dans le schéma

    public function getAuthPassword()
    {
        return $this->mot_de_passe_hash ?? $this->password;
    }

    public function getActiveClientAttribute()
    {
        return $this->active_client_id ? Client::find($this->active_client_id) : null;
    }

    /**
     * Vérifie si l'utilisateur possède un rôle spécifique pour l'entreprise active.
     * @param string $roleCode
     * @return bool
     */
    public function hasRoleForActiveEntreprise($roleCode)
    {
        $activeEntrepriseId = session('active_entreprise_id');
        if (!$activeEntrepriseId) {
            return false;
        }

        // Si l'utilisateur a déjà été vérifié par le middleware, la requête contient les affectations
        $affectations = request()->get('user_affectations');
        if ($affectations) {
            return $affectations->contains(function ($affectation) use ($roleCode) {
                return $affectation->role && $affectation->role->code === $roleCode;
            });
        }

        // Sinon, requête BDD
        return $this->affectations()
            ->where('entreprise_id', $activeEntrepriseId)
            ->where('statut', 'active')
            ->whereHas('role', function ($query) use ($roleCode) {
                $query->where('code', $roleCode);
            })->exists();
    }

    public function getNameAttribute()
    {
        return $this->nom;
    }

    public function isAutonomousSecretary(): bool
    {
        // Autonomous if flagged OR has no cabinet assigned
        return (bool)($this->is_autonomous) || $this->cabinet_id === null;
    }

    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class, 'utilisateur_id');
    }

    public function entreprises(): BelongsToMany
    {
        return $this->belongsToMany(Entreprise::class, 'affectations', 'utilisateur_id', 'entreprise_id')
                    ->withPivot(['role_id', 'modele', 'statut', 'expire_le']);
    }

    public function userClients(): HasMany
    {
        return $this->hasMany(UserClient::class, 'user_id');
    }

    /**
     * Vérifie si l'utilisateur est un Super Admin global (rôle système).
     */
    public function isSuperAdmin(): bool
    {
        // Check by role field first (simple & fast), then fallback to affectations
        $role = strtolower($this->role ?? '');
        if (in_array($role, ['super_admin', 'superadmin', 'admin'])) {
            return true;
        }
        try {
            return $this->affectations()
                ->whereHas('role', function($q) {
                    $q->where('code', 'SUPER_ADMIN');
                })->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Vérifie si l'utilisateur a la permission requise sur l'entreprise donnée.
     */
    public function hasPermissionTo(string $module, string $action, ?string $entrepriseId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$entrepriseId) {
            return false;
        }

        // Récupérer l'affectation active de l'utilisateur pour cette entreprise
        $affectation = $this->affectations()
            ->where('entreprise_id', $entrepriseId)
            ->whereIn('statut', ['actif', 'active'])
            ->first();

        if (!$affectation) {
            return false;
        }

        // Vérifier si une permission explicite autorise l'action
        return $affectation->permissions()
            ->where('ressource', $module)
            ->where('action', $action)
            ->where('autorise', true)
            ->exists();
    }

    /**
     * Récupère le niveau hiérarchique du rôle principal de l'utilisateur (diagramme 26.1).
     * 100 = Super Admin, 90 = Director, ..., 10 = Client
     */
    public function roleLevel(): int
    {
        $levels = [
            'super_admin' => 100,
            'director' => 90,
            'pole_responsible' => 60,
            'fiscaliste' => 55,
            'comptable' => 50,
            'collaborator' => 45,
            'company_admin' => 40,
            'company_manager' => 30,
            'secretaire' => 20,
            'company_employee' => 20,
            'caissier' => 20,
            'juriste' => 20,
            'rh' => 20,
            'gestionnaire_projet' => 20,
            'auditeur' => 15,
            'client' => 10,
        ];
        
        return $levels[$this->role] ?? 0;
    }

    /**
     * Vérifie si cet utilisateur a un rang strictement supérieur à un autre utilisateur.
     */
    public function canManage(User $otherUser): bool
    {
        if ($this->isSuperAdmin()) return true;
        return $this->roleLevel() > $otherUser->roleLevel();
    }
}
