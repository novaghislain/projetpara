<?php

namespace App\Services\Accounting;

use App\Models\AccountingAccount;
use App\Models\AccountingMorgueDepot;
use App\Models\AccountingPressingCommande;
use App\Models\Client;

/**
 * Service de calcul des KPI spécifiques par domaine d'activité.
 *
 * Chaque domaine (commerce, hôtel, scolaire, etc.) expose des indicateurs
 * de performance adaptés, calculés à partir des soldes comptables
 * (plan SYSCOHADA) et des modèles métier spécifiques.
 */
class DomainKpiService
{
    /**
     * Calcule les KPIs pour un client en fonction de son domaine.
     */
    public function getKpis(Client $client): array
    {
        $clientId = $client->id;
        $domainCode = $client->domain_code;

        // Soldes des comptes de résultat via journaux
        $compteSolde = function ($prefix) use ($clientId) {
            $accounts = AccountingAccount::where('client_id', $clientId)
                ->where('code', 'like', $prefix . '%')
                ->get();
            return (float) $accounts->reduce(function ($carry, $a) {
                return $carry + ((float) $a->debit_total) - ((float) $a->credit_total);
            }, 0);
        };

        $soldeCompte = function ($code) use ($clientId) {
            $account = AccountingAccount::where('client_id', $clientId)
                ->where('code', $code)->first();
            if (!$account) return 0;
            return (float) $account->balance;
        };

        // Soldes trésorerie (521 Banques + 571 Caisse)
        $tresorerie = $compteSolde('5');
        if ($tresorerie == 0) {
            $tresorerie = ($soldeCompte('521') ?: 0) + ($soldeCompte('571') ?: 0);
        }

        // Compte clients (411)
        $creances = $soldeCompte('411') ?: $compteSolde('41');

        // Compte fournisseurs (401)
        $dettes = $soldeCompte('401') ?: $compteSolde('40');

        return match ($domainCode) {
            'commerce' => $this->commerceKpis($compteSolde, $soldeCompte, $tresorerie, $creances, $dettes),
            'hotel'    => $this->hotelKpis($compteSolde, $soldeCompte, $tresorerie, $creances),
            'scolaire' => $this->scolaireKpis($compteSolde, $soldeCompte, $tresorerie, $creances),
            'location' => $this->locationKpis($compteSolde, $soldeCompte, $tresorerie, $creances),
            'tontine'  => $this->tontineKpis($compteSolde, $tresorerie),
            'transport'=> $this->transportKpis($compteSolde, $soldeCompte, $tresorerie, $creances),
            'pressing' => $this->pressingKpis($clientId, $compteSolde, $tresorerie, $creances),
            'morgue'   => $this->morgueKpis($clientId, $compteSolde, $tresorerie, $creances),
            'restauration' => $this->restaurationKpis($compteSolde, $soldeCompte, $tresorerie, $creances),
            'industrie' => $this->industrieKpis($compteSolde, $soldeCompte, $tresorerie, $creances, $dettes),
            'sante'    => $this->santeKpis($compteSolde, $soldeCompte, $tresorerie, $creances),
            'cabinet_comptable' => $this->cabinetKpis($compteSolde, $tresorerie, $creances),
            default    => $this->genericKpis($compteSolde, $tresorerie, $creances),
        };
    }

    /**
     * Commerce et Distribution
     */
    private function commerceKpis(callable $ca, callable $sc, float $tr, float $cr, float $dt): array
    {
        $chiffreAffaires = $ca('701') ?: $ca('70');
        $achats = $ca('601') ?: $ca('60');
        return [
            'ca_mensuel'          => round($chiffreAffaires, 2),
            'achats_mensuel'      => round($achats, 2),
            'marge_brute'         => round($chiffreAffaires - $achats, 2),
            'taux_marge'          => $chiffreAffaires > 0
                ? round((($chiffreAffaires - $achats) / $chiffreAffaires) * 100, 1) : 0,
            'tresorerie'          => round($tr, 2),
            'creances_clients'    => round($cr, 2),
            'dettes_fournisseurs' => round($dt, 2),
            'stock_valorise'      => round($ca('3'), 2),
            'tva_a_payer'         => round($sc('441'), 2),
        ];
    }

    /**
     * Hôtel et Hébergement
     */
    private function hotelKpis(callable $ca, callable $sc, float $tr, float $cr): array
    {
        $recettes = $ca('706');
        return [
            'recettes_nuitees'    => round($recettes, 2),
            'taux_occupation'     => null, // nécessite données chambres
            'revpar'              => null,
            'taxe_nuitee_due'     => round($sc('447'), 2),
            'tresorerie'          => round($tr, 2),
            'creances_clients'    => round($cr, 2),
            'tva_a_payer'         => round($sc('441'), 2),
        ];
    }

    /**
     * Établissement Scolaire
     */
    private function scolaireKpis(callable $ca, callable $sc, float $tr, float $cr): array
    {
        $recettes = $ca('706');
        return [
            'recettes_scolarite'  => round($recettes, 2),
            'frais_percus'        => round($recettes, 2),
            'impayes'             => round($cr, 2),
            'tresorerie'          => round($tr, 2),
            'charges_totales'     => round($ca('6'), 2),
            'tva_a_payer'         => round($sc('441'), 2),
        ];
    }

    /**
     * Location Immobilière
     */
    private function locationKpis(callable $ca, callable $sc, float $tr, float $cr): array
    {
        $loyers = $ca('703');
        return [
            'loyers_du_mois'      => round($loyers, 2),
            'loyers_encaisses'    => round($loyers - $cr, 2),
            'taux_recouvrement'   => $loyers > 0
                ? round(($loyers - $cr) / $loyers * 100, 1) : null,
            'impayes_total'       => round($cr, 2),
            'tresorerie'          => round($tr, 2),
        ];
    }

    /**
     * Tontine et Finance Participative
     */
    private function tontineKpis(callable $ca, float $tr): array
    {
        $cotisations = $ca('70');
        return [
            'cotisations_mois'    => round($cotisations, 2),
            'total_cagnotte'      => round($cotisations, 2),
            'tresorerie'          => round($tr, 2),
        ];
    }

    /**
     * Transport et Transit
     */
    private function transportKpis(callable $ca, callable $sc, float $tr, float $cr): array
    {
        $recettes = $ca('706');
        $carburant = $ca('605') ?: $ca('606');
        return [
            'ca_fret_mois'        => round($recettes, 2),
            'charges_carburant'   => round($carburant, 2),
            'marge_transport'     => round($recettes - $carburant, 2),
            'tresorerie'          => round($tr, 2),
            'creances'            => round($cr, 2),
        ];
    }

    /**
     * Pressing et Blanchisserie
     */
    private function pressingKpis(int $clientId, callable $ca, float $tr, float $cr): array
    {
        $recettes = $ca('706') ?: $ca('70');
        $commandesEnCours = AccountingPressingCommande::forClient($clientId)
            ->whereIn('statut', ['en_cours', 'pret'])
            ->count();
        $commandesMois = AccountingPressingCommande::forClient($clientId)
            ->whereMonth('date_depot', now()->month)
            ->whereYear('date_depot', now()->year)
            ->count();

        return [
            'recettes_pressing'   => round($recettes, 2),
            'commandes_en_cours'  => $commandesEnCours,
            'commandes_du_mois'   => $commandesMois,
            'tresorerie'          => round($tr, 2),
            'creances_clients'    => round($cr, 2),
        ];
    }

    /**
     * Morgue et Pompes Funèbres
     */
    private function morgueKpis(int $clientId, callable $ca, float $tr, float $cr): array
    {
        $recettes = $ca('706') ?: $ca('70');
        $depotsActifs = AccountingMorgueDepot::forClient($clientId)
            ->where('statut', 'actif')
            ->count();
        $depotsMois = AccountingMorgueDepot::forClient($clientId)
            ->whereMonth('date_depot', now()->month)
            ->whereYear('date_depot', now()->year)
            ->count();

        return [
            'recettes_morgue'     => round($recettes, 2),
            'depots_actifs'       => $depotsActifs,
            'depots_du_mois'      => $depotsMois,
            'tresorerie'          => round($tr, 2),
            'creances'            => round($cr, 2),
        ];
    }

    /**
     * Restauration et Bar
     */
    private function restaurationKpis(callable $ca, callable $sc, float $tr, float $cr): array
    {
        $recettes = $ca('706') ?: $ca('70');
        $achatsMarchandises = $ca('601') ?: $ca('60');
        $stock = $ca('3');
        return [
            'ca_restauration'     => round($recettes, 2),
            'achats_denrees'      => round($achatsMarchandises, 2),
            'marge_brute'         => round($recettes - $achatsMarchandises, 2),
            'stock_valorise'      => round($stock, 2),
            'tresorerie'          => round($tr, 2),
            'creances_clients'    => round($cr, 2),
            'tva_a_payer'         => round($sc('441'), 2),
        ];
    }

    /**
     * Industrie et Production
     */
    private function industrieKpis(callable $ca, callable $sc, float $tr, float $cr, float $dt): array
    {
        $ventes = $ca('701') ?: $ca('70');
        $achatsMp = $ca('601'); // matières premières
        $produitsFabriques = $ca('702') ?: $ventes;
        $stock = $ca('3'); // stocks (MP + PF)
        return [
            'ca_industrie'        => round($ventes, 2),
            'production'          => round($produitsFabriques, 2),
            'achats_matieres'     => round($achatsMp, 2),
            'valeur_ajoutee'      => round($ventes - $achatsMp, 2),
            'stock_valorise'      => round($stock, 2),
            'tresorerie'          => round($tr, 2),
            'creances_clients'    => round($cr, 2),
            'dettes_fournisseurs' => round($dt, 2),
            'tva_a_payer'         => round($sc('441'), 2),
        ];
    }

    /**
     * Santé et Clinique
     */
    private function santeKpis(callable $ca, callable $sc, float $tr, float $cr): array
    {
        $recettes = $ca('706') ?: $ca('70');
        $stockMedicaments = $ca('3');
        return [
            'recettes_sante'      => round($recettes, 2),
            'stock_pharmacie'     => round($stockMedicaments, 2),
            'tresorerie'          => round($tr, 2),
            'creances_clients'    => round($cr, 2),
            'tva_a_payer'         => round($sc('441'), 2),
        ];
    }

    /**
     * Cabinet d'Expertise Comptable
     */
    private function cabinetKpis(callable $ca, float $tr, float $cr): array
    {
        $honoraires = $ca('706');
        return [
            'honoraires_mois'     => round($honoraires, 2),
            'tresorerie'          => round($tr, 2),
            'creances_clients'    => round($cr, 2),
        ];
    }

    /**
     * KPIs génériques pour tout domaine non spécifique.
     */
    private function genericKpis(callable $ca, float $tr, float $cr): array
    {
        return [
            'ca_total'            => round($ca('7'), 2),
            'charges_total'       => round($ca('6'), 2),
            'resultat'            => round($ca('7') - $ca('6'), 2),
            'tresorerie'          => round($tr, 2),
            'creances_clients'    => round($cr, 2),
        ];
    }
}
