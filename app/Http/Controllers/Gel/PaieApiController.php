<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Services\Paie\ItScalculator;
use App\Services\Paie\CnssCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API de calcul de la paie (ITS + ORTB + CNSS).
 * Utilisé par la Vue calculateur.
 *
 * L'impôt sur les traitements et salaires (ITS, CGI 2026 — jamais « ITS »)
 * est calculé sur le barème mensuel progressif (0/10/15/19/30 %), majoré de la
 * redevance ORTB (mars / juin) et des avantages en nature (article 123).
 */
class PaieApiController extends Controller
{
    public function __construct(
        private readonly ItScalculator $its,
        private readonly CnssCalculator $cnss,
    ) {}

    /**
     * Calcule le salaire net après ITS, ORTB et CNSS.
     *
     * @param Request $request salaire_brut, salaire_base (optionnel), mois (1-12),
     *                         cadre (bool), avantages (liste de codes),
     *                         avantages_reels (montant), situation (ignorée, ITS
     *                         sans quotient familial).
     * @return JsonResponse Le détail du calcul (brut, net, ITS, ORTB, avantages, CNSS)
     */
    public function calculer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'salaire_brut'     => 'required|numeric|min:0',
            'salaire_base'     => 'nullable|numeric|min:0',
            'mois'             => 'nullable|integer|min:1|max:12',
            'cadre'            => 'nullable|boolean',
            'avantages'        => 'nullable|array',
            'avantages.*'      => 'string|in:logement,domesticite,electricite,eau,telephone,nourriture,vehicule4,vehicule2',
            'avantages_reels'  => 'nullable|numeric|min:0',
        ]);

        $brut  = (float) $validated['salaire_brut'];
        $base  = (float) ($validated['salaire_base'] ?? $brut);
        $mois  = (int) ($validated['mois'] ?? 0);
        $cadre = (bool) ($validated['cadre'] ?? false);
        $avantagesActifs = $validated['avantages'] ?? [];
        $avantagesReels  = (float) ($validated['avantages_reels'] ?? 0);

        // ITS (barème mensuel progressif) + ORTB (mars/juin) + avantages nature
        $itsResult = $this->its->calculerPaieITS(
            $brut,
            $base,
            $mois,
            $cadre,
            $avantagesActifs,
            $avantagesReels
        );

        // CNSS (plafond mensuel 450 000 FCFA)
        $cnssResult = $this->cnss->calculate($brut);

        // Salaire net = brut − part salariale CNSS − ITS − ORTB
        $retenues = ($cnssResult['part_salarie'] ?? 0)
                  + ($itsResult['its_mensuel'] ?? 0)
                  + ($itsResult['ortb']['montant'] ?? 0);
        $salaireNet = max(0, $brut - $retenues);

        return response()->json([
            'salaire_brut' => $brut,
            'salaire_net'  => round($salaireNet, 0),
            'its'          => $itsResult,
            'cnss'         => $cnssResult,
        ]);
    }
}