<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle UserClient - Association utilisateur-entreprise (pivot).
 *
 * Table associée : 'user_clients'.
 * Lie un utilisateur à une entreprise (Client) avec un rôle spécifique,
 * le statut d'activation, et les dates de jonction/demier accès.
 * Relations :
 * - user() : appartient à un utilisateur (User).
 * - client() : appartient à un client (Client).
 * - inviter() : appartient à l'utilisateur (User) qui a invité.
 */
class UserClient extends Model
{
    protected $table = 'user_clients';

    protected $fillable = [
        'user_id',
        'client_id',
        'role',
        'is_active',
        'invited_by',
        'joined_at',
        'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'joined_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
