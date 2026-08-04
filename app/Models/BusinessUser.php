<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un utilisateur métier d'une entreprise cliente.
 *
 * Lie un utilisateur (User) à un client (entreprise) avec un rôle
 * (BusinessRole) et des permissions spécifiques. Permet la gestion
 * fine des droits d'accès au sein d'une entreprise cliente.
 *
 * @property int $id
 * @property int $client_id Identifiant du client (entreprise)
 * @property int $user_id Identifiant de l'utilisateur
 * @property int $role_id Identifiant du rôle métier
 * @property string|null $business_role Rôle métier (texte libre)
 * @property bool $is_active Utilisateur actif
 * @property array $permissions Permissions spécifiques (JSON)
 * @property int|null $created_by Identifiant du créateur
 *
 * @property-read Client $client Client associé
 * @property-read User $user Utilisateur associé
 * @property-read BusinessRole $role Rôle métier
 * @property-read User|null $creator Créateur
 * @property-read \Illuminate\Database\Eloquent\Collection|PosSession[] $posSessions Sessions PDV
 * @property-read \Illuminate\Database\Eloquent\Collection|Sale[] $sales Ventes associées
 *
 * @table business_users
 */
class BusinessUser extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'role_id',
        'business_role',
        'is_active',
        'permissions',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(BusinessRole::class, 'role_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function posSessions(): HasMany
    {
        return $this->hasMany(PosSession::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
