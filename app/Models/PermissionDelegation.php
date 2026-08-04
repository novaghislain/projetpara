<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle PermissionDelegation - Délégation de permissions entre utilisateurs.
 *
 * Table associée : 'permission_delegations' (convention Laravel).
 * Permet à un utilisateur de déléguer temporairement ses permissions à un autre.
 * Relations :
 * - Aucune relation définie pour l'instant (modèle simple).
 */
class PermissionDelegation extends Model
{
    //
}
