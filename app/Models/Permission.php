<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Permission (Permission).
 *
 * Définit les permissions associées aux modules et actions du système.
 * Chaque permission est liée à des rôles via une relation many-to-many
 * (table pivot `role_permission`).
 *
 * @property int $id
 * @property string $module Module concerné
 * @property string $action Action (create, read, update, delete, etc.)
 * @property string $display_name Nom affichable
 * @property string|null $description Description
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles Rôles ayant cette permission
 */
class Permission extends Model
{
    protected $fillable = [
        'module',
        'action',
        'display_name',
        'description',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->withTimestamps();
    }

    /**
     * Scope: permissions pour un module spécifique.
     */
    public function scopeModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope: permissions pour plusieurs modules.
     */
    public function scopeModules($query, array $modules)
    {
        return $query->whereIn('module', $modules);
    }
}
