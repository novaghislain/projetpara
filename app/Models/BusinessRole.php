<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un rôle métier (RBAC).
 *
 * Définit les rôles et permissions associés pour un client (entreprise).
 * Chaque rôle peut être attribué à plusieurs utilisateurs métier
 * via BusinessUser. Les permissions sont stockées en JSON.
 *
 * @property int $id
 * @property int $client_id Identifiant du client (entreprise)
 * @property string $role Nom du rôle (admin, comptable, etc.)
 * @property array $permissions Permissions associées (JSON)
 * @property bool $is_active Rôle actif
 *
 * @property-read Client $client Client associé
 * @property-read \Illuminate\Database\Eloquent\Collection|BusinessUser[] $businessUsers Utilisateurs ayant ce rôle
 *
 * @table business_roles
 */
class BusinessRole extends Model
{
    protected $fillable = [
        'client_id',
        'role',
        'permissions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function businessUsers(): HasMany
    {
        return $this->hasMany(BusinessUser::class, 'role_id');
    }
}
