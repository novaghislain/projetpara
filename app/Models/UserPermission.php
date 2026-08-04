<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle UserPermission - Permission directe attribuée à un utilisateur.
 *
 * Table associée : 'user_permissions'.
 * Permet d'attribuer une permission spécifique directement à un utilisateur,
 * sans passer par un rôle. Utile pour les cas d'exception.
 * Relations :
 * - user() : appartient à un utilisateur (User).
 * - permission() : appartient à une permission (Permission).
 * - grantedBy() : appartient à l'utilisateur (User) qui a accordé la permission.
 */
class UserPermission extends Model
{
    protected $fillable = [
        'user_id',
        'permission_id',
        'granted_by',
        'granted_at',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
