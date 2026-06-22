<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseGelAccountingController extends Controller
{
    /**
     * Récupère le client_id depuis la requête.
     *
     * Priorité :
     * 1. Paramètre de route {clientId}
     * 2. Champ client_id du body de la requête
     *
     * abort(403) si aucun client trouvé.
     */
    protected function getClientId(Request $request): int
    {
        $clientId = $request->route('clientId') ?? $request->input('client_id');

        if (!$clientId) {
            abort(403, 'Aucun client sélectionné.');
        }

        return (int) $clientId;
    }
}
