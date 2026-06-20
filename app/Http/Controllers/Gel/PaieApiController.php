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
    public function __construct(
        private readonly IrppCalculator $irpp,
        private readonly CnssCalculator $cnss,
    ) {}

    public function calculer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'salaire_brut' => 'required|numeric|min:0',
            'situation'    => 'nullable|string|in:celibataire,marie_sans_enf,marie_1_enf,marie_2_enf,marie_3_enf',
        ]);

        $salaire = (float) $validated['salaire_brut'];
        $situation = $validated['situation'] ?? 'celibataire';

        $irppResult = $this->irpp->calculateMonthly($salaire, $situation);
        $cnssResult = $this->cnss->calculate($salaire);

        $salaireNet = $salaire - ($irppResult['irpp_mensuel'] ?? 0) - ($cnssResult['part_salarie'] ?? 0);

        return response()->json([
            'salaire_brut' => $salaire,
            'salaire_net'  => max(0, $salaireNet),
            'irpp'         => $irppResult,
            'cnss'         => $cnssResult,
        ]);
    }
}
