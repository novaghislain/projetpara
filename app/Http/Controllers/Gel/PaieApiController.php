<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Services\Paie\IrppCalculator;
use App\Services\Paie\CnssCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API de calcul de la paie (IRPP + CNSS).
 * Utilisé par la Vue calculateur.
 */
class PaieApiController extends Controller
{
    /**
     * Contrôleur API de calcul de la paie.
     * Calcule l'IRPP (Impôt sur le Revenu des Personnes Physiques)
     * et les cotisations CNSS à partir du salaire brut mensuel.
     * Utilisé par le calculateur de paie côté Vue.
     */

    public function __construct(
        private readonly IrppCalculator $irpp,
        private readonly CnssCalculator $cnss,
    ) {}

    /**
     * Calcule le salaire net après IRPP et CNSS.
     *
     * @param Request $request La requête HTTP avec le salaire brut et la situation familiale
     * @return JsonResponse Le détail du calcul (salaire brut, net, IRPP, CNSS)
     */
    public function calculer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'salaire_brut' => 'required|numeric|min:0',
            'situation'    => 'nullable|string|in:celibataire,marie_sans_enf,marie_1_enf,marie_2_enf,marie_3_enf',
        ]);

        $salaire = (float) $validated['salaire_brut'];
        $situation = $validated['situation'] ?? 'celibataire';

        // Calculs IRPP et CNSS via les services dédiés
        $irppResult = $this->irpp->calculateMonthly($salaire, $situation);
        $cnssResult = $this->cnss->calculate($salaire);

        // Salaire net = brut - IRPP - part salariale CNSS
        $salaireNet = $salaire - ($irppResult['irpp_mensuel'] ?? 0) - ($cnssResult['part_salarie'] ?? 0);

        return response()->json([
            'salaire_brut' => $salaire,
            'salaire_net'  => max(0, $salaireNet),
            'irpp'         => $irppResult,
            'cnss'         => $cnssResult,
        ]);
    }
}
