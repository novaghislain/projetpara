<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle AccountType (Type de compte utilisateur).
 *
 * Définit les différents types de comptes utilisateur (ex: administrateur,
 * comptable, superviseur) avec un code, un libellé et une description.
 * Table associée : `gel_account_types`.
 *
 * @property string $code Code unique du type de compte
 * @property string $libelle Libellé du type de compte
 * @property string|null $description Description détaillée
 * @property bool $actif Si le type est actif
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users Utilisateurs ayant ce type de compte
 */
class AccountType extends Model
{
    protected $table = 'gel_account_types';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'account_type', 'code');
    }
}
