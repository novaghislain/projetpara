<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
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
