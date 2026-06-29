<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Base controller pour les endpoints API.
 *
 * Centralise la récupération du client_id pour les API appelées
 * dans le contexte d'une entreprise (Company).
 */
abstract class BaseApiController extends Controller
{
    /**
     * Récupère le client_id de l'utilisateur authentifié.
     */
    protected function getClientId(): int
    {
        $user = Auth::user();
        if (!$user || !$user->client_id) {
            abort(403, 'Aucune entreprise associée.');
        }
        return (int) $user->client_id;
    }
}
