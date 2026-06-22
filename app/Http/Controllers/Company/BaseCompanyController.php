<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Base controller pour les controllers de l'interface Company (entreprise).
 *
 * Centralise la récupération du client_id et la vérification de propriété
 * client, éliminant la duplication dans chaque controller Company.
 *
 * @see \App\Http\Middleware\EnsureCompanyAccess  (middleware associé)
 */
abstract class BaseCompanyController extends Controller
{
    /**
     * Récupère l'ID du client actif pour l'utilisateur connecté.
     *
     * Respecte la même logique que EnsureCompanyAccess :
     * - active_client_id si défini (contexte multi-entreprise),
     * - fallback sur client_id (entreprise par défaut).
     */
    protected function getClientId(): int
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Non authentifié.');
        }

        $clientId = $user->active_client_id ?? $user->client_id;

        if (!$clientId) {
            abort(403, 'Aucune entreprise associée à votre compte.');
        }

        return (int) $clientId;
    }

    /**
     * Vérifie explicitement que le client_id passé appartient bien
     * à l'utilisateur connecté.
     *
     * Utilisation : vérifier qu'un paramètre de route (ou autre ID
     * externe) correspond bien au client de l'utilisateur, empêchant
     * toute tentative d'accès跨-client même accidentelle.
     */
    protected function authorizeClient(?int $targetClientId = null): void
    {
        $userClientId = $this->getClientId();

        if ($targetClientId !== null && (int) $targetClientId !== $userClientId) {
            abort(403, 'Action non autorisée pour cette entreprise.');
        }
    }
}
