<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Models\Legal\LegalRegistre;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des registres légaux obligatoires.
 *
 * Gère les registres des assemblées, des décisions, des apports
 * et autres registres requis par la réglementation SYSCOHADA et OHADA.
 */
class LegalRegistresController extends BaseLegalController
{
    /**
     * Affiche la liste des registres légaux.
     *
     * Retourne les registres triés par type et année décroissante pour le client connecté.
     *
     * @param Request $request La requête HTTP entrante
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-registres']);
        }
        $clientId = $this->getClientId($request);
        return response()->json(
            LegalRegistre::byClient($clientId)->orderBy('type')->orderBy('annee', 'desc')->get()
        );
    }

    /**
     * Affiche le contenu d'un registre légal spécifique.
     *
     * @param string $type Le type de registre (ex: assembly, decisions)
     * @param int $annee L'année du registre
     * @param Request $request La requête HTTP entrante
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($type, $annee, Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-registres-show']);
        }
        $clientId = $this->getClientId($request);
        $registre = LegalRegistre::byClient($clientId)
            ->where('type', $type)
            ->where('annee', $annee)
            ->firstOrFail();

        return response()->json($registre);
    }

    /**
     * Ajoute une entrée dans un registre légal.
     *
     * Crée le registre s'il n'existe pas encore, puis ajoute l'entrée
     * avec son numéro séquentiel, son objet et ses détails.
     *
     * @param Request $request La requête HTTP contenant l'objet et les détails de l'entrée
     * @param string $type Le type de registre
     * @param int $annee L'année du registre
     * @return \Illuminate\Http\JsonResponse
     */
    public function addEntry(Request $request, $type, $annee)
    {
        $clientId = $this->getClientId($request);

        $registre = LegalRegistre::firstOrCreate(
            [
                'client_id' => $clientId,
                'type' => $type,
                'annee' => $annee,
            ],
            ['entrees' => []]
        );

        $entrees = $registre->entrees ?? [];
        $entrees[] = [
            'date' => now()->format('Y-m-d'),
            'numero' => count($entrees) + 1,
            'objet' => $request->objet,
            'details' => $request->details,
        ];

        $registre->update(['entrees' => $entrees]);

        return response()->json(['success' => true, 'data' => $registre]);
    }

    /**
     * Exporte le contenu d'un registre légal.
     *
     * @param string $type Le type de registre
     * @param int $annee L'année du registre
     * @param Request $request La requête HTTP entrante
     * @return \Illuminate\Http\JsonResponse
     */
    public function export($type, $annee, Request $request)
    {
        $clientId = $this->getClientId($request);
        $registre = LegalRegistre::byClient($clientId)
            ->where('type', $type)
            ->where('annee', $annee)
            ->firstOrFail();

        return response()->json($registre);
    }
}
