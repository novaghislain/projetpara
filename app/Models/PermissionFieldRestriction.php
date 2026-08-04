<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle PermissionFieldRestriction - Restriction de champs par module/action/rôle.
 *
 * Table associée : 'permission_field_restrictions'.
 * Permet de masquer certains champs de l'interface utilisateur en fonction
 * du module, de l'action et du rôle de l'utilisateur.
 * Relations :
 * - creator() : appartient à l'utilisateur (User) qui a créé la restriction.
 */
class PermissionFieldRestriction extends Model
{
    protected $table = 'permission_field_restrictions';

    protected $fillable = [
        'module',
        'action',
        'role_slug',
        'hidden_fields',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'hidden_fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
