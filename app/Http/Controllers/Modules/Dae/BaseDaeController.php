<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de base pour le module DAE (Document, Archivage, Échange).
 *
 * Fournit les fonctionnalités communes partagées par les contrôleurs du module DAE,
 * notamment la récupération de l'identifiant client depuis la requête.
 */
abstract class BaseDaeController extends Controller
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
