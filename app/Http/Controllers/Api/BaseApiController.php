<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Classe de base pour les contrôleurs API.
 *
 * Centralise la récupération de l'ID du client connecté pour
 * les endpoints API opérant dans un contexte d'entreprise.
 */
abstract class BaseApiController extends Controller
{
    /**
     * Récupère le client_id de l'utilisateur authentifié.
     *
     * @return int
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
