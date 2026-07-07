<?php

namespace App\Services\IA;

use App\Models\AccountingAccount;
use App\Models\Client;
use App\Models\JournalEntry;
use App\Models\EntryLine;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\AiSuggestion;
use App\Models\AiLearningLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinanceAiService
{
    /**
     * Génère la balance générale pour une période
     */
    public function getBalance(int $clientId, int $fiscalYearId): array
    {
        // Récupérer les comptes actifs du client
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->where('is_active', true)
            ->get();

        // Récupérer les IDs des périodes de cet exercice
        $periodIds = FiscalPeriod::where('fiscal_year_id', $fiscalYearId)->pluck('id');

        if ($periodIds->isEmpty()) return [];

        // Lignes d'écritures de l'exercice
        $lines = EntryLine::where('client_id', $clientId)
            ->whereHas('entry', function ($q) use ($periodIds) {
                $q->whereIn('fiscal_period_id', $periodIds);
            })->get();

        $balance = [];
        foreach ($accounts as $account) {
            $accountLines = $lines->where('account_id', $account->id);
            $totalDebit = $accountLines->sum('debit');
            $totalCredit = $accountLines->sum('credit');
            $solde = $totalDebit - $totalCredit;

            $balance[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'classe' => substr($account->code, 0, 1),
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'solde' => $solde,
                'solde_abs' => abs($solde),
                'nature' => $solde >= 0 ? 'debiteur' : 'crediteur',
            ];
        }

        return $balance;
    }

    /**
     * Calcule les ratios financiers à partir des états financiers
     */
    public function calculateRatios(int $clientId, int $fiscalYearId): array
    {
        $balance = $this->getBalance($clientId, $fiscalYearId);
        $totals = $this->aggregateByClasse($balance);

        $actifImmobilise = $totals['classe_2'] ?? 0;
        $actifCirculant = ($totals['classe_3'] ?? 0) + ($totals['classe_4_debiteur'] ?? 0);
        $tresorerieActif = $totals['classe_5'] ?? 0;
        $capitauxPropres = $totals['classe_1'] ?? 0;
        $passifCirculant = $totals['classe_4_crediteur'] ?? 0;
        $tresoreriePassif = 0; // Dettes financières < 1 an

        $charges = $totals['classe_6'] ?? 0;
        $produits = $totals['classe_7'] ?? 0;
        $resultatNet = $produits - $charges;
        $totalActif = $actifImmobilise + $actifCirculant + $tresorerieActif;

        // Ratios de liquidité
        $liquiditeGenerale = $passifCirculant > 0 ? ($actifCirculant + $tresorerieActif) / $passifCirculant : null;
        $liquiditeReduite = $passifCirculant > 0 ? $actifCirculant / $passifCirculant : null;
        $liquiditeImmediate = $passifCirculant > 0 ? $tresorerieActif / $passifCirculant : null;

        // Ratios de rentabilité
        $rentabiliteNet = $produits > 0 ? ($resultatNet / $produits) * 100 : null;
        $rentabiliteEconomique = $totalActif > 0 ? ($resultatNet / $totalActif) * 100 : null;
        $rentabiliteFinanciere = $capitauxPropres > 0 ? ($resultatNet / $capitauxPropres) * 100 : null;

        // Ratios de solvabilité
        $endettementGlobal = $capitauxPropres > 0 ? ($passifCirculant + $tresoreriePassif) / $capitauxPropres : null;
        $autonomieFinanciere = $totalActif > 0 ? ($capitauxPropres / $totalActif) * 100 : null;

        // Ratios d'activité
        $rotationCreances = $produits > 0 ? ($totals['clientele'] ?? 0) / ($produits / 365) : null;
        $rotationDettes = $charges > 0 ? ($totals['fournisseurs'] ?? 0) / ($charges / 365) : null;

        $ratios = [
            'liquidite' => [
                'liquidite_generale' => [
                    'value' => $liquiditeGenerale,
                    'label' => 'Liquidité générale',
                    'interpretation' => $this->interpretRatio($liquiditeGenerale, 'liquidite_generale'),
                    'seuil_min' => 1.0,
                    'statut' => $this->getRatioStatus($liquiditeGenerale, 1.0),
                ],
                'liquidite_reduite' => [
                    'value' => $liquiditeReduite,
                    'label' => 'Liquidité réduite',
                    'interpretation' => $this->interpretRatio($liquiditeReduite, 'liquidite_reduite'),
                    'seuil_min' => 0.7,
                    'statut' => $this->getRatioStatus($liquiditeReduite, 0.7),
                ],
                'liquidite_immediate' => [
                    'value' => $liquiditeImmediate,
                    'label' => 'Liquidité immédiate',
                    'interpretation' => $this->interpretRatio($liquiditeImmediate, 'liquidite_immediate'),
                    'seuil_min' => 0.3,
                    'statut' => $this->getRatioStatus($liquiditeImmediate, 0.3),
                ],
            ],
            'rentabilite' => [
                'rentabilite_nette' => [
                    'value' => $rentabiliteNet,
                    'label' => 'Rentabilité nette',
                    'interpretation' => $this->interpretRatio($rentabiliteNet, 'rentabilite'),
                    'seuil_min' => 5.0,
                    'statut' => $this->getRatioStatus($rentabiliteNet, 5.0),
                ],
                'rentabilite_economique' => [
                    'value' => $rentabiliteEconomique,
                    'label' => 'Rentabilité économique',
                    'interpretation' => $this->interpretRatio($rentabiliteEconomique, 'rentabilite'),
                    'seuil_min' => 8.0,
                    'statut' => $this->getRatioStatus($rentabiliteEconomique, 8.0),
                ],
                'rentabilite_financiere' => [
                    'value' => $rentabiliteFinanciere,
                    'label' => 'Rentabilité financière (ROE)',
                    'interpretation' => $this->interpretRatio($rentabiliteFinanciere, 'rentabilite'),
                    'seuil_min' => 10.0,
                    'statut' => $this->getRatioStatus($rentabiliteFinanciere, 10.0),
                ],
            ],
            'solvabilite' => [
                'endettement_global' => [
                    'value' => $endettementGlobal,
                    'label' => 'Endettement global',
                    'interpretation' => $this->interpretRatio($endettementGlobal, 'endettement'),
                    'seuil_max' => 1.0,
                    'statut' => $endettementGlobal !== null
                        ? ($endettementGlobal <= 1.0 ? 'bon' : 'alerte')
                        : 'non_disponible',
                ],
                'autonomie_financiere' => [
                    'value' => $autonomieFinanciere,
                    'label' => 'Autonomie financière',
                    'interpretation' => $this->interpretRatio($autonomieFinanciere, 'autonomie'),
                    'seuil_min' => 30.0,
                    'statut' => $this->getRatioStatus($autonomieFinanciere, 30.0),
                ],
            ],
            'activite' => [
                'rotation_creances' => [
                    'value' => $rotationCreances,
                    'label' => 'Rotation des créances (jours)',
                    'interpretation' => $this->interpretRatio($rotationCreances, 'rotation_creances'),
                    'seuil_max' => 60,
                    'statut' => $rotationCreances !== null
                        ? ($rotationCreances <= 60 ? 'bon' : 'alerte')
                        : 'non_disponible',
                ],
                'rotation_dettes' => [
                    'value' => $rotationDettes,
                    'label' => 'Rotation des dettes (jours)',
                    'interpretation' => $this->interpretRatio($rotationDettes, 'rotation_dettes'),
                    'seuil_max' => 45,
                    'statut' => $rotationDettes !== null
                        ? ($rotationDettes <= 45 ? 'bon' : 'alerte')
                        : 'non_disponible',
                ],
            ],
            'synthese' => [
                'resultat_net' => $resultatNet,
                'resultat_net_formatted' => number_format($resultatNet, 0, ',', ' ') . ' FCFA',
                'total_actif' => $totalActif,
                'total_actif_formatted' => number_format($totalActif, 0, ',', ' ') . ' FCFA',
                'capitaux_propres' => $capitauxPropres,
                'capitaux_propres_formatted' => number_format($capitauxPropres, 0, ',', ' ') . ' FCFA',
                'chiffre_affaires' => $produits,
                'chiffre_affaires_formatted' => number_format($produits, 0, ',', ' ') . ' FCFA',
            ],
        ];

        return $ratios;
    }

    /**
     * Agrège les comptes par classe SYSCOHADA
     */
    private function aggregateByClasse(array $balance): array
    {
        $totals = [
            'classe_1' => 0,
            'classe_2' => 0,
            'classe_3' => 0,
            'classe_4_debiteur' => 0,
            'classe_4_crediteur' => 0,
            'classe_5' => 0,
            'classe_6' => 0,
            'classe_7' => 0,
            'clientele' => 0,
            'fournisseurs' => 0,
        ];

        foreach ($balance as $item) {
            $classe = $item['classe'];
            $solde = $item['solde'];

            if (isset($totals["classe_{$classe}"])) {
                if ($classe === '4') {
                    if ($solde > 0) {
                        $totals['classe_4_debiteur'] += $solde;
                    } else {
                        $totals['classe_4_crediteur'] += abs($solde);
                    }
                } elseif (in_array($classe, ['6', '7'])) {
                    // Classes 6 et 7 : on prend les totaux (débit pour charges, crédit pour produits)
                    $totals["classe_{$classe}"] += $item['total_debit'];
                } else {
                    $totals["classe_{$classe}"] += $solde;
                }
            }

            // Comptes spécifiques clientèle (411) et fournisseurs (401)
            if (str_starts_with($item['account_code'], '411')) {
                $totals['clientele'] += $solde;
            }
            if (str_starts_with($item['account_code'], '401')) {
                $totals['fournisseurs'] += abs($solde);
            }
        }

        return $totals;
    }

    /**
     * Interprète un ratio en langage naturel
     */
    private function interpretRatio(?float $value, string $type): string
    {
        if ($value === null) return 'Non calculable (données insuffisantes)';

        return match ($type) {
            'liquidite_generale' => $value >= 1.5
                ? 'Très bonne capacité à rembourser les dettes à court terme (≥ 1.5)'
                : ($value >= 1.0
                    ? 'Capacité suffisante à couvrir les dettes à court terme'
                    : 'Risque de difficultés de trésorerie (< 1.0)'),
            'liquidite_reduite' => $value >= 1.0
                ? 'Bonne capacité à couvrir les dettes sans les stocks'
                : 'Dépendance aux stocks pour couvrir les dettes',
            'liquidite_immediate' => $value >= 0.5
                ? 'Trésorerie immédiate suffisante'
                : 'Trésorerie faible',
            'rentabilite' => $value >= 15
                ? 'Excellente rentabilité (> 15%)'
                : ($value >= 8
                    ? 'Bonne rentabilité (8-15%)'
                    : ($value >= 5
                        ? 'Rentabilité correcte (5-8%)'
                        : 'Rentabilité faible, à surveiller')),
            'endettement' => $value <= 1.0
                ? "Niveau d'endettement maîtrisé (≤ 1.0)"
                : "Endettement élevé (> 1.0), risque de surendettement",
            'autonomie' => $value >= 50
                ? 'Très bonne autonomie financière (≥ 50%)'
                : ($value >= 30
                    ? 'Autonomie financière correcte (30-50%)'
                    : 'Dépendance financière aux créanciers'),
            'rotation_creances' => $value <= 30
                ? 'Très bon recouvrement (≤ 30 jours)'
                : ($value <= 60
                    ? 'Recouvrement correct (30-60 jours)'
                    : 'Délai de recouvrement trop long (> 60 jours)'),
            'rotation_dettes' => $value <= 30
                ? 'Paiement rapide des fournisseurs (≤ 30 jours)'
                : ($value <= 45
                    ? 'Paiement correct (30-45 jours)'
                    : 'Paiement lent, risque de pénalités'),
            default => 'Ratio calculé',
        };
    }

    private function getRatioStatus(?float $value, float $seuil): string
    {
        if ($value === null) return 'non_disponible';
        return $value >= $seuil ? 'bon' : 'alerte';
    }

    /**
     * Prédiction de trésorerie sur N jours
     */
    public function predictCashFlow(int $clientId, int $days = 90): array
    {
        $now = now();
        $predictions = [];

        // Analyser les entrées/sorties des 6 derniers mois
        $sixMonthsAgo = $now->copy()->subMonths(6);
        $historicalLines = EntryLine::where('client_id', $clientId)
            ->whereHas('entry', function ($q) use ($sixMonthsAgo) {
                $q->where('entry_date', '>=', $sixMonthsAgo);
            })->get();

        // Calculer les moyennes par jour de semaine
        $dailyAverages = [];
        for ($i = 0; $i < 7; $i++) {
            $dayLines = $historicalLines->filter(fn($l) => $l->entry && $l->entry->entry_date->dayOfWeek === $i);
            $dailyAverages[$i] = [
                'entree' => $dayLines->sum('credit'),
                'sortie' => $dayLines->sum('debit'),
                'count' => $dayLines->count(),
            ];
        }

        // Prévisions
        $soldeInitial = $this->getSoldeTresorerie($clientId);
        $soldeCourant = $soldeInitial;

        for ($day = 0; $day < $days; $day++) {
            $date = $now->copy()->addDays($day);
            $dow = $date->dayOfWeek;

            // Sauter les week-ends
            if ($dow === 0 || $dow === 6) continue;

            $avg = $dailyAverages[$dow] ?? ['entree' => 0, 'sortie' => 0, 'count' => 0];
            $entreePrevue = $avg['count'] > 0 ? $avg['entree'] / max($avg['count'], 1) : 0;
            $sortiePrevue = $avg['count'] > 0 ? $avg['sortie'] / max($avg['count'], 1) : 0;

            // Ajouter les échéances connues (factures, paie, etc.)
            $echeances = $this->getEcheancesConnues($clientId, $date);
            $entreePrevue += $echeances['entrees'];
            $sortiePrevue += $echeances['sorties'];

            $soldeCourant += $entreePrevue - $sortiePrevue;

            $predictions[] = [
                'date' => $date->format('Y-m-d'),
                'entree_prevue' => round($entreePrevue),
                'sortie_prevue' => round($sortiePrevue),
                'solde_prevu' => round($soldeCourant),
                'jour' => $date->locale('fr')->dayName,
                'alerte' => $soldeCourant < 0 ? 'decouvert' : ($soldeCourant < 500000 ? 'faible' : null),
            ];
        }

        // Analyse des risques
        $joursNegatifs = collect($predictions)->filter(fn($p) => $p['solde_prevu'] < 0);
        $soldeMinimum = collect($predictions)->min('solde_prevu');
        $soldeMaximum = collect($predictions)->max('solde_prevu');

        return [
            'client_id' => $clientId,
            'date_analyse' => $now->format('Y-m-d'),
            'solde_actuel' => $soldeInitial,
            'solde_estime_90j' => $soldeCourant,
            'solde_minimum' => $soldeMinimum,
            'solde_maximum' => $soldeMaximum,
            'jours_negatifs' => $joursNegatifs->count(),
            'jours_alerte' => collect($predictions)->filter(fn($p) => $p['alerte'])->count(),
            'predictions' => $predictions,
            'resume' => $this->resumeCashFlow($soldeInitial, $soldeCourant, $soldeMinimum, $joursNegatifs->count()),
        ];
    }

    /**
     * Solde actuel des comptes de trésorerie (classe 5)
     */
    private function getSoldeTresorerie(int $clientId): float
    {
        $comptesTresorerie = AccountingAccount::where('client_id', $clientId)
            ->where('code', 'like', '5%')
            ->where('is_active', true)
            ->pluck('id');

        if ($comptesTresorerie->isEmpty()) return 0;

        $totalDebit = EntryLine::whereIn('account_id', $comptesTresorerie)
            ->where('client_id', $clientId)
            ->sum('debit');
        $totalCredit = EntryLine::whereIn('account_id', $comptesTresorerie)
            ->where('client_id', $clientId)
            ->sum('credit');

        return $totalDebit - $totalCredit;
    }

    /**
     * Récupère les échéances connues pour une date donnée
     * Tables echeances et paie_bulletins peuvent ne pas exister → try-catch
     */
    private function getEcheancesConnues(int $clientId, Carbon $date): array
    {
        $entrees = 0;
        $sorties = 0;
        $dateStr = $date->format('Y-m-d');

        // Échéances clients (si table existe)
        try {
            $echeancesClients = DB::table('echeances')
                ->where('client_id', $clientId)
                ->whereDate('date_echeance', $dateStr)
                ->where('type', 'client')
                ->get();

            foreach ($echeancesClients as $echeance) {
                $entrees += (float) ($echeance->montant ?? 0);
            }
        } catch (\Exception $e) {
            // Table echeances non existante — ignoré
        }

        // Échéances fournisseurs (si table existe)
        try {
            $echeancesFournisseurs = DB::table('echeances')
                ->where('client_id', $clientId)
                ->whereDate('date_echeance', $dateStr)
                ->where('type', 'fournisseur')
                ->get();

            foreach ($echeancesFournisseurs as $echeance) {
                $sorties += (float) ($echeance->montant ?? 0);
            }
        } catch (\Exception $e) {
            // Table echeances non existante — ignoré
        }

        // Paie (25 ou 30 du mois, si table existe)
        if ($date->day === 25 || $date->day === 30) {
            try {
                $payroll = DB::table('paie_bulletins')
                    ->where('client_id', $clientId)
                    ->whereMonth('periode', $date->month)
                    ->whereYear('periode', $date->year)
                    ->sum('net_a_payer');
                $sorties += (float) $payroll;
            } catch (\Exception $e) {
                // Table paie_bulletins non existante — ignoré
            }
        }

        return ['entrees' => $entrees, 'sorties' => $sorties];
    }

    /**
     * Résumé textuel des prévisions de trésorerie
     */
    private function resumeCashFlow(float $soldeInitial, float $soldeFinal, float $soldeMin, int $joursNegatifs): string
    {
        $parts = [];

        if ($soldeInitial < 0) {
            $parts[] = '🔴 Trésorerie initiale négative de ' . number_format(abs($soldeInitial), 0, ',', ' ') . ' FCFA.';
        }

        $variation = $soldeFinal - $soldeInitial;
        if ($variation > 0) {
            $parts[] = '📈 Amélioration prévue de ' . number_format($variation, 0, ',', ' ') . ' FCFA sur 90 jours.';
        } elseif ($variation < 0) {
            $parts[] = '📉 Dégradation prévue de ' . number_format(abs($variation), 0, ',', ' ') . ' FCFA sur 90 jours.';
        }

        if ($soldeMin < 0) {
            $parts[] = '⚠️ Le solde minimum atteindra ' . number_format($soldeMin, 0, ',', ' ') .
                ' FCFA. Préparez un découvert ou une avance de trésorerie.';
        }

        if ($joursNegatifs > 0) {
            $parts[] = "⚠️ {$joursNegatifs} jour(s) avec solde négatif prévu.";
        } else {
            $parts[] = '✅ Aucun jour négatif prévu. Situation saine.';
        }

        return implode(' ', $parts);
    }

    /**
     * Génère une analyse en langage naturel des états financiers
     */
    public function generateFinancialAnalysis(int $clientId, int $fiscalYearId): array
    {
        $ratios = $this->calculateRatios($clientId, $fiscalYearId);
        $synthese = $ratios['synthese'];

        $sections = [];

        // Résultat
        if ($synthese['resultat_net'] > 0) {
            $sections[] = [
                'title' => 'Résultat net',
                'icon' => '✅',
                'content' => "Résultat bénéficiaire de **{$synthese['resultat_net_formatted']}**.",
                'statut' => 'positif',
            ];
        } elseif ($synthese['resultat_net'] < 0) {
            $sections[] = [
                'title' => 'Résultat net',
                'icon' => '🔴',
                'content' => "Résultat déficitaire de **{$synthese['resultat_net_formatted']}**.",
                'statut' => 'negatif',
            ];
        } else {
            $sections[] = [
                'title' => 'Résultat net',
                'icon' => '⚪',
                'content' => 'Résultat à l\'équilibre.',
                'statut' => 'neutre',
            ];
        }

        // Analyse liquidité
        $lg = $ratios['liquidite']['liquidite_generale'];
        $lgStatus = $lg['statut'] === 'bon' ? 'positif' : 'negatif';
        $lgIcon = $lg['statut'] === 'bon' ? '✅' : '🔴';
        $lgContent = $lg['value'] !== null
            ? "Liquidité générale à **" . number_format($lg['value'], 2) . "** — "
                . ($lg['statut'] === 'bon' ? 'bonne capacité de remboursement.' : 'risque de tensions de trésorerie.')
            : 'Liquidité générale non calculable (données insuffisantes).';
        $sections[] = [
            'title' => 'Liquidité',
            'icon' => $lgIcon,
            'content' => $lgContent,
            'statut' => $lgStatus,
        ];

        // Analyse rentabilité
        $rn = $ratios['rentabilite']['rentabilite_nette'];
        $rnStatus = $rn['statut'] === 'bon' ? 'positif' : 'alerte';
        $rnIcon = $rn['statut'] === 'bon' ? '✅' : '⚠️';
        $rnContent = $rn['value'] !== null
            ? "Rentabilité nette à **" . number_format($rn['value'], 1) . "%** — "
                . ($rn['statut'] === 'bon'
                    ? 'performance satisfaisante.'
                    : 'en dessous du seuil recommandé de 5%.')
            : 'Rentabilité nette non calculable.';
        $sections[] = [
            'title' => 'Rentabilité',
            'icon' => $rnIcon,
            'content' => $rnContent,
            'statut' => $rnStatus,
        ];

        // Analyse solvabilité
        $eg = $ratios['solvabilite']['endettement_global'];
        $egStatus = $eg['statut'] === 'bon' ? 'positif' : 'alerte';
        $egIcon = $eg['statut'] === 'bon' ? '✅' : '⚠️';
        $egContent = $eg['value'] !== null
            ? "Endettement à **" . number_format($eg['value'], 2) . "** — "
                . ($eg['statut'] === 'bon' ? 'maîtrisé.' : 'au-dessus du seuil de 1.0.')
            : "Niveau d'endettement non calculable.";
        $sections[] = [
            'title' => 'Solvabilité',
            'icon' => $egIcon,
            'content' => $egContent,
            'statut' => $egStatus,
        ];

        // Recommandations
        $recommandations = [];
        if ($synthese['resultat_net'] < 0) {
            $recommandations[] = "Réduire les charges d'exploitation ou augmenter les prix.";
        }
        if ($lg['statut'] === 'alerte') {
            $recommandations[] = 'Renforcer le fonds de roulement (apport en capital ou crédit à moyen terme).';
        }
        if ($eg['statut'] === 'alerte') {
            $recommandations[] = "Renégocier les dettes pour allonger les échéances.";
        }
        if ($synthese['resultat_net'] > 0 && $lg['statut'] === 'bon' && $eg['statut'] === 'bon') {
            $recommandations[] = 'Situation financière saine. Envisager des investissements de croissance.';
        }

        return [
            'synthese' => $synthese,
            'sections' => $sections,
            'recommandations' => $recommandations,
            'texte_complet' => $this->buildTexteComplet($synthese, $sections, $recommandations),
        ];
    }

    /**
     * Construit le texte complet en markdown
     */
    private function buildTexteComplet(array $synthese, array $sections, array $recommandations): string
    {
        $lines = [];
        $lines[] = '📊 **Analyse financière GEL Cabinet**';
        $lines[] = '';

        // Chiffres clés
        $lines[] = '**🔑 Chiffres clés :**';
        $lines[] = "• Chiffre d'affaires : {$synthese['chiffre_affaires_formatted']}";
        $lines[] = "• Résultat net : {$synthese['resultat_net_formatted']}";
        $lines[] = "• Total actif : {$synthese['total_actif_formatted']}";
        $lines[] = "• Capitaux propres : {$synthese['capitaux_propres_formatted']}";
        $lines[] = '';

        foreach ($sections as $section) {
            $lines[] = "{$section['icon']} **{$section['title']} :**";
            $lines[] = "   {$section['content']}";
            $lines[] = '';
        }

        if (!empty($recommandations)) {
            $lines[] = '**💡 Recommandations :**';
            foreach ($recommandations as $rec) {
                $lines[] = "• {$rec}";
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Détecte des alertes financières
     */
    public function detectAlerts(int $clientId, int $fiscalYearId): array
    {
        $alerts = [];
        $ratios = $this->calculateRatios($clientId, $fiscalYearId);
        $cashFlow = $this->predictCashFlow($clientId, 30);

        // Alerte 1 : Trésorerie négative prévue
        if (!empty($cashFlow['predictions'])) {
            $negativeDays = collect($cashFlow['predictions'])->filter(fn($p) => $p['solde_prevu'] < 0);
            if ($negativeDays->isNotEmpty()) {
                $nextNegative = $negativeDays->first();
                $alerts[] = [
                    'type' => 'tresorerie_negative',
                    'severity' => 'critical',
                    'title' => 'Découvert bancaire prévu',
                    'message' => "Solde négatif prévu le {$nextNegative['date']} ({$nextNegative['jour']}).",
                    'detail' => "Action recommandée : débloquer une ligne de trésorerie ou reporter des paiements.",
                ];
            }
        }

        // Alerte 2 : Ratio de liquidité sous seuil
        $lg = $ratios['liquidite']['liquidite_generale'];
        if ($lg['statut'] === 'alerte' && $lg['value'] !== null) {
            $alerts[] = [
                'type' => 'liquidite_faible',
                'severity' => 'high',
                'title' => 'Liquidité générale insuffisante',
                'message' => 'Ratio de liquidité à ' . number_format($lg['value'], 2) . ' (seuil minimum : 1.0).',
                'detail' => 'Risque de difficultés à payer les dettes à court terme.',
            ];
        }

        // Alerte 3 : Rentabilité négative
        $resultat = $ratios['synthese']['resultat_net'];
        if ($resultat < 0) {
            $alerts[] = [
                'type' => 'perte_nette',
                'severity' => 'high',
                'title' => 'Exercice déficitaire',
                'message' => 'Le résultat net est négatif : ' . number_format(abs($resultat), 0, ',', ' ') . ' FCFA.',
                'detail' => 'Analyse : charges supérieures aux produits. Vérifier les postes de charges.',
            ];
        }

        // Alerte 4 : Endettement élevé
        $eg = $ratios['solvabilite']['endettement_global'];
        if ($eg['statut'] === 'alerte' && $eg['value'] !== null) {
            $alerts[] = [
                'type' => 'endettement_eleve',
                'severity' => 'high',
                'title' => 'Endettement excessif',
                'message' => "Ratio d'endettement à " . number_format($eg['value'], 2) . ' (seuil max : 1.0).',
                'detail' => 'Risque de surendettement. Envisager un désendettement.',
            ];
        }

        // Alerte 5 : Rotation des créances trop longue
        $rc = $ratios['activite']['rotation_creances'];
        if ($rc['statut'] === 'alerte' && $rc['value'] !== null) {
            $alerts[] = [
                'type' => 'creances_lentes',
                'severity' => 'moyenne',
                'title' => 'Recouvrement des créances lent',
                'message' => 'Rotation des créances à ' . number_format($rc['value'], 1) . ' jours.',
                'detail' => 'Action : relancer les clients en retard.',
            ];
        }

        return $alerts;
    }

    /**
     * Enregistre l'apprentissage continu
     */
    public function logLearning(array $data): void
    {
        AiLearningLog::create(array_merge(
            ['agent' => 'finance'],
            $data
        ));
    }
}
