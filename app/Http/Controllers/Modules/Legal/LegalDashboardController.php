<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Models\Legal\LegalContract;
use App\Models\Legal\LegalAssembly;
use App\Models\Legal\LegalLitigation;
use App\Models\Legal\LegalCompliance;
use App\Models\Legal\LegalDossier;
use Illuminate\Http\Request;

/**
 * Contrôleur du tableau de bord du module Juridique.
 *
 * Fournit les indicateurs clés et les statistiques pour la vue
 * d'ensemble des activités juridiques de l'entreprise.
 */
class LegalDashboardController extends BaseLegalController
{
    /**
     * Affiche la page du tableau de bord juridique.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('app', ['page' => 'legal-dashboard']);
    }

    /**
     * Retourne les statistiques et indicateurs du tableau de bord juridique.
     *
     * Calcule et renvoie les données agrégées : contrats actifs, contentieux en cours,
     * conformités, assemblées planifiées et dossiers ouverts.
     *
     * @param Request $request La requête HTTP entrante
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request)
    {
        $clientId = $this->getClientId($request);

        return response()->json([
            'stats' => [
                'contrats_actifs' => LegalContract::byClient($clientId)->whereIn('statut', ['signé', 'actif'])->count(),
                'contrats_expiration' => LegalContract::byClient($clientId)->expireBientot(30)->count(),
                'contentieux_en_cours' => LegalLitigation::byClient($clientId)->enCours()->count(),
                'conformites_ok' => LegalCompliance::byClient($clientId)->where('statut', 'conforme')->count(),
                'conformites_alertes' => LegalCompliance::byClient($clientId)->whereIn('statut', ['non_conforme', 'expiré'])->count(),
                'ag_planifiees' => LegalAssembly::byClient($clientId)->planifiees()->count(),
                'dossiers_ouverts' => LegalDossier::byClient($clientId)->where('statut', 'en_cours')->count(),
            ],
            'contrats_urgents' => LegalContract::byClient($clientId)->expireBientot(15)->get(),
            'prochaines_ag' => LegalAssembly::byClient($clientId)->planifiees()->where('date_tenue', '<=', now()->addDays(30))->get(),
            'conformites_alertes' => LegalCompliance::byClient($clientId)->echeantes()->get(),
            'contentieux_recents' => LegalLitigation::byClient($clientId)->enCours()->orderBy('updated_at', 'desc')->limit(5)->get(),
        ]);
    }
}
