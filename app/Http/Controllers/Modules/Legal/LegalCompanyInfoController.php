<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Models\Legal\LegalCompanyInfo;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des informations de la société.
 *
 * Permet la consultation et la mise à jour des données légales
 * et administratives de l'entreprise cliente.
 */
class LegalCompanyInfoController extends BaseLegalController
{
    /**
     * Affiche les informations légales de la société.
     *
     * Pour une requête AJAX, retourne les données de la société associée au client connecté.
     *
     * @param Request $request La requête HTTP entrante
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-societe']);
        }
        $clientId = $this->getClientId($request);
        $info = LegalCompanyInfo::byClient($clientId)->first();

        return response()->json($info);
    }

    /**
     * Met à jour les informations légales de la société.
     *
     * Crée un nouvel enregistrement s'il n'existe pas encore pour ce client,
     * ou met à jour l'enregistrement existant.
     *
     * @param Request $request La requête HTTP avec les données de la société
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $clientId = $this->getClientId($request);

        $info = LegalCompanyInfo::byClient($clientId)->firstOrNew(['client_id' => $clientId]);
        $info->fill($request->all());
        $info->save();

        return response()->json(['success' => true, 'data' => $info]);
    }
}
