<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Pole - Pôle / département d'une organisation.
 *
 * Table associée : 'poles' (convention Laravel).
 * Représente un pôle ou département fonctionnel (ex: comptabilité, juridique, etc.).
 * Relations :
 * - users() : a plusieurs utilisateurs (User).
 * - missions() : a plusieurs missions (Mission).
 * - clients() : appartient à plusieurs clients (Client) via la table pivot 'client_pole'.
 */
class Pole extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function missions()
    {
        return $this->hasMany(Mission::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_pole')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
