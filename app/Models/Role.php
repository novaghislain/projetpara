<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Role - Rôle utilisateur (système legacy).
 *
 * Table associée : 'roles' (convention Laravel).
 * Définit les rôles avec un niveau hiérarchique, un slug unique,
 * et un indicateur système/personnalisé.
 * Relations :
 * - permissions() : appartient à plusieurs permissions (Permission, pivot 'role_permission').
 * - users() : a plusieurs utilisateurs (User).
 */
class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'level' => 'integer',
        ];
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->select('permissions.*')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Vérifie si ce rôle a accès à un module (une quelconque action).
     */
    public function hasModule(string $module): bool
    {
        return $this->permissions()
            ->where('module', $module)
            ->exists();
    }

    /**
     * Vérifie si ce rôle a une permission spécifique.
     */
    public function hasPermission(string $module, string $action): bool
    {
        return $this->permissions()
            ->where('module', $module)
            ->where('action', $action)
            ->exists();
    }

    /**
     * Récupère toutes les permissions pour un module donné.
     */
    public function modulePermissions(string $module)
    {
        return $this->permissions()->where('module', $module)->get();
    }

    /**
     * Scope: rôles système uniquement.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope: rôles non-système (personnalisés).
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }
}
