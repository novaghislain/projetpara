<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseGelAccountingController extends Controller
{
    /**
     * Contrôleur de base pour les fonctionnalités comptables.
     * Fournit des méthodes utilitaires partagées entre les contrôleurs
     * du module de comptabilité (AccountController, BudgetController,
     * ClosingController, JournalController, etc.).
     */

    /**
     * Récupère l'identifiant du client depuis la requête.
     *
     * Priorité :
     * 1. Paramètre de route {clientId}
     * 2. Champ client_id du body de la requête
     *
     * @param Request $request La requête HTTP entrante
     * @return int L'identifiant du client
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function getClientId(Request $request): int
    {
        // Extraction du client_id depuis la route ou le corps de la requête
        $clientId = $request->route('clientId') ?? $request->input('client_id');

        if (!$clientId) {
            abort(403, 'Aucun client sélectionné.');
        }

        return (int) $clientId;
    }
}
