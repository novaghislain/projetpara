<?php

namespace App\Services\Paie;

/**
 * Calculateur IRPP (Impôt sur le Revenu des Personnes Physiques)
 * Barème progressif Bénin 2026 — Tranches annuelles
 *
 * Ce service implémente le barème officiel à 7 tranches
 * avec abattement forfaitaire de 20% et quotient familial.
 */
class IrppCalculator
{
    /**
     * Barème progressif IRPP Bénin (annuel en FCFA)
     * Source : CGI Bénin 2026
     */
    const BAREME_IRPP = [
        ['min' => 0,         'max' => 60000,    'taux' => 0.00],
        ['min' => 60001,     'max' => 150000,   'taux' => 0.10],
        ['min' => 150001,    'max' => 250000,   'taux' => 0.15],
        ['min' => 250001,    'max' => 500000,   'taux' => 0.19],
        ['min' => 500001,    'max' => 1000000,  'taux' => 0.24],
        ['min' => 1000001,   'max' => 2000000,  'taux' => 0.28],
        ['min' => 2000001,   'max' => PHP_INT_MAX, 'taux' => 0.32],
    ];

    /**
     * Abattement forfaitaire : 20% du brut, plancher 200 000 FCFA, plafond 600 000 FCFA/an.
     */
    const ABATTEMENT_PCT = 0.20;
    const ABATTEMENT_MIN = 200000;
    const ABATTEMENT_MAX = 600000;

    /**
     * Nombre de parts pour le quotient familial.
     */
    const PARTS = [
        'celibataire'       => 1,
        'marie_sans_enf'    => 2,
        'marie_1_enf'       => 2.5,
        'marie_2_enf'       => 3,
        'marie_3_enf'       => 3.5,
    ];

    /**
     * Calculer l'IRPP annuel et mensuel.
     *
     * @param float $salaireBrutAnnuel Salaire brut annuel en FCFA
     * @param string $situation Situation familiale
     * @param int $nombreCharges Nombre d'enfants à charge (si non défini par situation)
     * @return array
     */
    public function calculate(
        float $salaireBrutAnnuel,
        string $situation = 'celibataire',
        int $nombreCharges = 0
    ): array {
        // 1. Abattement forfaitaire 20%
        $abattement = max(
            self::ABATTEMENT_MIN,
            min(self::ABATTEMENT_MAX, $salaireBrutAnnuel * self::ABATTEMENT_PCT)
        );
        $revenuImposable = max(0, $salaireBrutAnnuel - $abattement);

        // 2. Calcul IRPP par tranches
        $tranches = $this->calculerTranches($revenuImposable);
        $irppAnnuel = array_sum(array_column($tranches, 'impot'));

        // 3. Quotient familial
        $nbParts = $this->getNombreParts($situation, $nombreCharges);
        $irppParPart = $nbParts > 0 ? $irppAnnuel / $nbParts : $irppAnnuel;

        return [
            'salaire_brut'      => round($salaireBrutAnnuel, 0),
            'abattement'        => round($abattement, 0),
            'revenu_imposable'  => round($revenuImposable, 0),
            'tranches'          => $tranches,
            'irpp_annuel'       => round($irppParPart, 0),
            'irpp_mensuel'      => round($irppParPart / 12, 0),
            'nb_parts'          => $nbParts,
            'situation'         => $situation,
        ];
    }

    /**
     * Calculer l'IRPP mensuel directement.
     */
    public function calculateMonthly(
        float $salaireBrutMensuel,
        string $situation = 'celibataire',
        int $nombreEnfants = 0
    ): array {
        $annuel = $this->calculate($salaireBrutMensuel * 12, $situation, $nombreEnfants);
        $annuel['salaire_brut_mensuel'] = round($salaireBrutMensuel, 0);
        return $annuel;
    }

    /**
     * Répartir le revenu imposable dans les tranches du barème.
     */
    private function calculerTranches(float $revenuImposable): array
    {
        $tranches = [];
        foreach (self::BAREME_IRPP as $t) {
            if ($revenuImposable <= $t['min']) {
                break;
            }
            $baseTranche = min($revenuImposable, $t['max']) - $t['min'];
            $impotTranche = round($baseTranche * $t['taux'], 0);
            $tranches[] = [
                'min'   => $t['min'],
                'max'   => $t['max'],
                'taux'  => $t['taux'] * 100, // en % pour l'affichage
                'base'  => round($baseTranche, 0),
                'impot' => $impotTranche,
            ];
        }
        return $tranches;
    }

    /**
     * Déterminer le nombre de parts du quotient familial.
     */
    private function getNombreParts(string $situation, int $nombreCharges): float
    {
        if (isset(self::PARTS[$situation])) {
            return self::PARTS[$situation];
        }
        // Fallback : 1 part + 0.5 par charge
        return max(1, 1 + ($nombreCharges * 0.5));
    }
}
