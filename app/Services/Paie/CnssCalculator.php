<?php

namespace App\Services\Paie;

/**
 * Calculateur CNSS (Caisse Nationale de Sécurité Sociale)
 * Cotisations Bénin 2026
 *
 * Barème officiel :
 * - Taux employeur : 15,40% (prestations familiales + accidents du travail + retraite)
 * - Taux salarié   : 3,36% (retraite part salarié)
 * - Plafond mensuel : 450 000 FCFA
 */
class CnssCalculator
{
    const TAUX_EMPLOYEUR    = 0.1540; // 15,40 %
    const TAUX_SALARIE      = 0.0336; // 3,36 %
    const PLAFOND_MENSUEL   = 450000;  // 450 000 FCFA

    /**
     * Calculer les cotisations CNSS pour un salaire mensuel.
     *
     * @param float $salaireBrutMensuel Salaire brut mensuel en FCFA
     * @return array
     */
    public function calculate(float $salaireBrutMensuel): array
    {
        $assiette = min($salaireBrutMensuel, self::PLAFOND_MENSUEL);

        $partEmployeur = round($assiette * self::TAUX_EMPLOYEUR, 0);
        $partSalarie   = round($assiette * self::TAUX_SALARIE, 0);
        $total         = $partEmployeur + $partSalarie;

        return [
            'salaire_brut'    => round($salaireBrutMensuel, 0),
            'assiette'        => round($assiette, 0),
            'plafond'         => self::PLAFOND_MENSUEL,
            'taux_employeur'  => self::TAUX_EMPLOYEUR * 100,
            'taux_salarie'    => self::TAUX_SALARIE * 100,
            'part_employeur'  => $partEmployeur,
            'part_salarie'    => $partSalarie,
            'total'           => $total,
        ];
    }

    /**
     * Calculer les cotisations CNSS pour un salaire annuel*
     * (en divisant par 12 pour l'assiette mensuelle).
     */
    public function calculateAnnual(float $salaireBrutAnnuel): array
    {
        $mensuel = $salaireBrutAnnuel / 12;
        $result = $this->calculate($mensuel);
        $result['salaire_brut_annuel'] = round($salaireBrutAnnuel, 0);
        $result['part_employeur_annuelle'] = $result['part_employeur'] * 12;
        $result['part_salarie_annuelle'] = $result['part_salarie'] * 12;
        $result['total_annuel'] = $result['total'] * 12;
        return $result;
    }
}
