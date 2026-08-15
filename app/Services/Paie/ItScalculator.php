<?php
// =============================================================================
// FICHIER : ItScalculator.php
// RÔLE    : Calculateur ITS (Impôt sur les Traitements et Salaires) — Bénin
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Implémentation conforme au Code Général des Impôts (CGI) 2026 :
//
//   • Base d'imposition          : Article 122 — traitements, émoluments,
//     salaires, pécule, gratifications, heures supplémentaires, avantages
//     professionnels en argent ou en nature, indemnités (y compris transport).
//     Exclusions : Article 120/123/124 (indemnités de licenciement hors congés
//     et préavis, allocations familiales, cotisations patronales de prévoyance
//     dans la limite de 1,5 fois la part patronale CNSS retraite, frais de
//     formation, assurance maladie, rémunérations de stagiaires réguliers…).
//
//   • Barème ITS mensuel          : Article 125 §1 — tranches progressives
//       0 % ≤ 60 000 | 10 % 60 001-150 000 | 15 % 150 001-250 000
//       19 % 250 001-500 000 | 30 % > 500 000
//     Pas de quotient familial, pas d'abattement forfaitaire : l'impôt est le
//     simple cumul par tranche de la base imposable mensuelle.
//
//   • Redevance ORTB              : Article 125 §2 — 1 000 F sur le salaire de
//     mars ; 3 000 F sur le salaire de juin ; exonéré du prélèvement de
//     3 000 F (juin) lorsque le revenu imposable n'excède pas 60 000 F.
//
//   • Avantages en nature         : Article 123 §3 — évalués forfaitairement
//     chaque mois (Logement / Domesticité = 15 % du salaire de base ;
//     Électricité, Eau, Téléphone, Nourriture, Véhicules 2 et 4 roues —
//     deux colonnes : Dirigeants & Cadres supérieurs / Autres employés).
//     Ils sont ajoutés à la base d'imposition (Article 122 §1).
// =============================================================================

namespace App\Services\Paie;

class ItScalculator
{
    /**
     * Barème ITS mensuel — CGI 2026, Article 125 §1.
     * Montants en FCFA, taux en % (progressif, par tranche).
     */
    const BAREME_ITS = [
        ['min' => 0,           'max' => 60000,   'taux' => 0.00],
        ['min' => 60001,       'max' => 150000,  'taux' => 0.10],
        ['min' => 150001,      'max' => 250000,  'taux' => 0.15],
        ['min' => 250001,      'max' => 500000,  'taux' => 0.19],
        ['min' => 500001,      'max' => PHP_INT_MAX, 'taux' => 0.30],
    ];

    /** Redevance ORTB — CGI 2026, Article 125 §2 (FCFA). */
    const ORTB_MARS = 1000;   // sur le salaire du mois de mars
    const ORTB_JUIN = 3000;   // sur le salaire du mois de juin
    /** Exonération du prélèvement de juin : revenu imposable ≤ 1re tranche. */
    const ORTB_EXONERATION_SEUIL = 60000;

    /**
     * Valeurs forfaitaires mensuelles des avantages en nature — CGI 2026,
     * Article 123 §3 (loi de finances 2024). Deux colonnes : cadres supérieurs
     * / employés. Logement et Domesticité = % du salaire de base.
     * Active(article 123 §2) : retenus à leur valeur réelle.
     */
    const AVANTAGES_NATURE = [
        'logement'    => ['type' => 'pct_base', 'pct' => 0.15, 'cadre' => 0,    'employe' => 0,    'libelle' => 'Logement'],
        'domesticite' => ['type' => 'pct_base', 'pct' => 0.15, 'cadre' => 0,    'employe' => 0,    'libelle' => 'Domesticité'],
        'electricite' => ['type' => 'fixe',     'cadre' => 50000, 'employe' => 20000, 'libelle' => 'Électricité'],
        'eau'         => ['type' => 'fixe',     'cadre' => 10000, 'employe' => 5000,  'libelle' => 'Eau'],
        'telephone'   => ['type' => 'fixe',     'cadre' => 15000, 'employe' => 5000,  'libelle' => 'Téléphone'],
        'nourriture'  => ['type' => 'fixe',     'cadre' => 50000, 'employe' => 30000, 'libelle' => 'Nourriture'],
        'vehicule4'   => ['type' => 'fixe',     'cadre' => 30000, 'employe' => 15000, 'libelle' => 'Véhicule à 4 roues'],
        'vehicule2'   => ['type' => 'fixe',     'cadre' => 10000, 'employe' => 5000,  'libelle' => 'Véhicule à 2 roues'],
    ];

    /**
     * Expose le barème ITS (pour affichage).
     */
    public function bareme(): array
    {
        return self::BAREME_ITS;
    }

    /**
     * Calcule l'ITS mensuel (cumul progressif par tranches) — Article 125 §1.
     *
     * @param float $salaireImposable  Base d'imposition mensuelle (brut imposable)
     * @return array{salaire_imposable: float, tranches: array, its_mensuel: float}
     */
    public function calculerITS(float $salaireImposable): array
    {
        $salaireImposable = max(0, (float) $salaireImposable);
        $tranches = [];
        $its = 0.0;

        foreach (self::BAREME_ITS as $t) {
            if ($salaireImposable <= $t['min']) {
                break;
            }
            $baseTranche = min($salaireImposable, $t['max']) - $t['min'];
            $impotTranche = round($baseTranche * $t['taux'], 0);
            $its += $impotTranche;

            // Seules les tranches à taux > 0 sont listées (la tranche 0 %
            // n'apporte aucun impôt) : si le revenu ≤ 60 000 F, la liste
            // reste vide et l'UI affiche « exonéré ».
            if ($t['taux'] > 0) {
                $tranches[] = [
                    'min'   => $t['min'],
                    'max'   => $t['max'],
                    'taux'  => $t['taux'] * 100, // % pour l'affichage
                    'base'  => round($baseTranche, 0),
                    'impot' => $impotTranche,
                ];
            }
        }

        return [
            'salaire_imposable' => round($salaireImposable, 0),
            'tranches'          => $tranches,
            'its_mensuel'       => round($its, 0),
        ];
    }

    /**
     * Calcule la redevance ORTB — Article 125 §2.
     *   - mars : 1 000 F (sans exonération prévue) ;
     *   - juin : 3 000 F, sauf si le revenu imposable n'excède pas 60 000 F.
     *
     * @param float $salaireImposable Base d'imposition mensuelle
     * @param int   $mois            1-12 (0 ou hors 3/6 → aucune redevance)
     * @return array{application: string, exoneré: bool, montant: float}
     */
    public function calculerOrtb(float $salaireImposable, int $mois): array
    {
        $mois = (int) $mois;

        if ($mois === 3) {
            return [
                'application' => 'mars',
                'exonere'     => false,
                'montant'     => (float) self::ORTB_MARS,
            ];
        }

        if ($mois === 6) {
            $exonere = (float) $salaireImposable <= self::ORTB_EXONERATION_SEUIL;
            return [
                'application' => 'juin',
                'exonere'     => $exonere,
                'montant'     => $exonere ? 0.0 : (float) self::ORTB_JUIN,
            ];
        }

        return [
            'application' => 'aucun',
            'exonere'     => false,
            'montant'     => 0.0,
        ];
    }

    /**
     * Évalue les avantages en nature retenus forfaitairement — Article 123 §3.
     * Logement et Domesticité = % du salaire de base (0 % si non fourni).
     *
     * @param float   $salaireBase    Salaire de base mensuel (base des 15 %)
     * @param bool    $cadre          true = Dirigeant/Cadre supérieur ; false = employé
     * @param array   $actifs         Liste des codes d'avantages présents
     *                                (logement, domesticite, electricite, eau,
     *                                telephone, nourriture, vehicule4, vehicule2)
     * @param float   $montantsLibres Avantages à valeur RÉELLE (article 123 §2) —
     *                                frais voyage, frais médicaux hors 80 % congé
     *                                maladie, scolarité enfants… (facultatif)
     * @return array{total: float, detail: array}
     */
    public function evaluerAvantages(
        float $salaireBase,
        bool $cadre,
        array $actifs = [],
        float $montantsLibres = 0.0
    ): array {
        $salaireBase = max(0, (float) $salaireBase);
        $actifs = array_values(array_unique($actifs));
        $detail = [];
        $total = 0.0;

        foreach ($actifs as $code) {
            $spec = self::AVANTAGES_NATURE[$code] ?? null;
            if (!$spec) {
                continue;
            }

            if ($spec['type'] === 'pct_base') {
                $montant = round($salaireBase * $spec['pct'], 0);
            } else {
                $montant = (float) ($cadre ? $spec['cadre'] : $spec['employe']);
            }

            $detail[] = [
                'code'      => $code,
                'libelle'   => $spec['libelle'],
                'montant'   => round($montant, 0),
                'regulation' => $spec['type'] === 'pct_base'
                    ? ($spec['pct'] * 100) . ' % du salaire de base'
                    : 'forfait mensuel',
            ];
            $total += $montant;
        }

        // Avantages à valeur réelle (article 123 §2) — évalués au réel.
        if ((float) $montantsLibres > 0) {
            $detail[] = [
                'code'       => 'valeur_reelle',
                'libelle'    => 'Autres avantages (valeur réelle)',
                'montant'    => round((float) $montantsLibres, 0),
                'regulation' => 'valeur réelle (article 123 §2)',
            ];
            $total += (float) $montantsLibres;
        }

        return [
            'total'  => round($total, 0),
            'detail' => $detail,
        ];
    }

    /**
     * Calcul complet d'une paie mensuelle soumise à l'ITS.
     *
     * @param float $salaireBrut     Brut mensuel (FCFA)
     * @param float $salaireBase     Salaire de base (base des avantages 15 %)
     * @param int   $mois            Mois (1-12) pour le prélèvement ORTB
     * @param bool  $cadre           Statut (dirigeant/cadre vs employé)
     * @param array $avantagesActifs Codes des avantages en nature reçus
     * @param float $avantagesReels  Avantages évalués à leur valeur réelle
     * @return array
     */
    public function calculerPaieITS(
        float $salaireBrut,
        float $salaireBase,
        int $mois,
        bool $cadre = false,
        array $avantagesActifs = [],
        float $avantagesReels = 0.0
    ): array {
        $avantages = $this->evaluerAvantages($salaireBase, $cadre, $avantagesActifs, $avantagesReels);

        // Base d'imposition : brut + avantages en nature (article 122 §1).
        $salaireImposable = max(0, (float) $salaireBrut + $avantages['total']);

        $its  = $this->calculerITS($salaireImposable);
        $ortb = $this->calculerOrtb($salaireImposable, $mois);

        return [
            'salaire_brut'      => round((float) $salaireBrut, 0),
            'salaire_base'      => round((float) $salaireBase, 0),
            'mois'              => (int) $mois,
            'cadre'             => (bool) $cadre,
            'avantages_nature'  => $avantages,
            'salaire_imposable' => $its['salaire_imposable'],
            'tranches'          => $its['tranches'],
            'its_mensuel'       => $its['its_mensuel'],
            'ortb'              => $ortb,
            'total_retenue_dgi' => round($its['its_mensuel'] + $ortb['montant'], 0),
        ];
    }
}