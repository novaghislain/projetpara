<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de base pour le module RH.
 *
 * Fournit des méthodes partagées aux contrôleurs du module RH,
 * notamment la récupération de l'identifiant client à partir
 * de la requête ou de l'utilisateur authentifié.
 */
abstract class BaseRhController extends Controller
{
    protected function getClientId(Request $request): int
    {
        $clientId = $request->input('client_id') ?: Auth::user()?->client_id;
        if (!$clientId) {
            abort(403, 'Aucune entreprise associée.');
        }
        return (int) $clientId;
    }
}
