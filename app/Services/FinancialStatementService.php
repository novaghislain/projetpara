<?php

namespace App\Services;

use App\Models\Gel\LigneEcriture;
use App\Models\Gel\CompteComptable;
use Illuminate\Support\Facades\DB;

class FinancialStatementService
{
    /**
     * Calcule la balance de tous les comptes pour le cabinet/client courant.
     * Renvoie un tableau avec le code du compte en clé, et le solde net (Débit - Crédit).
     * 
     * @param int $cabinetId
     * @param int|null $clientId
     * @param int|null $exerciceId
     * @return array
     */
    public function getBalancesByAccountCode($cabinetId, $clientId = null, $exerciceId = null)
    {
        $query = LigneEcriture::join('gel_ecritures', 'gel_lignes_ecriture.ecriture_id', '=', 'gel_ecritures.id')
            ->join('gel_comptes_comptables', 'gel_lignes_ecriture.compte_id', '=', 'gel_comptes_comptables.id')
            ->where('gel_ecritures.cabinet_id', $cabinetId)
            ->where('gel_ecritures.valide', true); // On ne prend que les écritures validées (ou toutes ?)

        if ($clientId) {
            $query->where('gel_ecritures.client_id', $clientId);
        } else {
            $query->whereNull('gel_ecritures.client_id');
        }

        if ($exerciceId) {
            $query->where('gel_ecritures.exercice_id', $exerciceId);
        }

        // Somme des montants par sens
        $balances = $query->select(
            'gel_comptes_comptables.code',
            DB::raw("SUM(CASE WHEN gel_lignes_ecriture.sens = 'debit' THEN gel_lignes_ecriture.montant ELSE 0 END) as total_debit"),
            DB::raw("SUM(CASE WHEN gel_lignes_ecriture.sens = 'credit' THEN gel_lignes_ecriture.montant ELSE 0 END) as total_credit")
        )
        ->groupBy('gel_comptes_comptables.code')
        ->get();

        $result = [];
        foreach ($balances as $balance) {
            $result[$balance->code] = [
                'debit' => (float) $balance->total_debit,
                'credit' => (float) $balance->total_credit,
                'solde' => (float) $balance->total_debit - (float) $balance->total_credit
            ];
        }

        return $result;
    }

    /**
     * Calcule la somme des soldes pour un préfixe de compte donné (ex: '2', '41', '601')
     * Si le solde attendu est débiteur (Actif/Charge), on renvoie (Débit - Crédit).
     * Si le solde attendu est créditeur (Passif/Produit), on renvoie (Crédit - Débit).
     */
    public function sumPrefix($balances, $prefixes, $expectedType = 'debit')
    {
        $sum = 0;
        if (!is_array($prefixes)) {
            $prefixes = [$prefixes];
        }

        foreach ($balances as $code => $data) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with((string)$code, (string)$prefix)) {
                    $sum += ($expectedType === 'debit') ? $data['solde'] : -$data['solde'];
                    break;
                }
            }
        }
        return $sum;
    }

    public function getBilanData($cabinetId, $clientId = null, $exerciceId = null)
    {
        $balances = $this->getBalancesByAccountCode($cabinetId, $clientId, $exerciceId);

        // ACTIF (Solde débiteur attendu)
        $actif = [
            'incorporelles' => $this->sumPrefix($balances, '21', 'debit'),
            'corporelles' => $this->sumPrefix($balances, ['22', '23', '24'], 'debit'),
            'financieres' => $this->sumPrefix($balances, ['26', '27'], 'debit'),
            'stocks' => $this->sumPrefix($balances, '3', 'debit'),
            'creances_clients' => $this->sumPrefix($balances, '41', 'debit'),
            'autres_creances' => $this->sumPrefix($balances, ['42', '43', '44', '45', '46'], 'debit'), // Approximatif
            'tresorerie' => $this->sumPrefix($balances, ['52', '53', '54', '57', '58'], 'debit'),
        ];
        $totalActif = array_sum($actif);

        // PASSIF (Solde créditeur attendu)
        $passif = [
            'capital' => $this->sumPrefix($balances, '10', 'credit'),
            'reserves' => $this->sumPrefix($balances, ['11', '12'], 'credit'),
            'report_nouveau' => $this->sumPrefix($balances, '13', 'credit'),
            'emprunts' => $this->sumPrefix($balances, '16', 'credit'),
            'fournisseurs' => $this->sumPrefix($balances, '40', 'credit'),
            'dettes_fiscales' => $this->sumPrefix($balances, ['42', '43', '44'], 'credit'),
            'decouverts' => $this->sumPrefix($balances, '56', 'credit'),
        ];
        
        // Résultat Net = Produits - Charges
        $produits = $this->sumPrefix($balances, '7', 'credit');
        $charges = $this->sumPrefix($balances, '6', 'debit');
        $resultatNet = $produits - $charges;
        
        $passif['resultat_net'] = $resultatNet;
        
        $totalPassif = array_sum($passif);

        return [
            'actif' => $actif,
            'passif' => $passif,
            'total_actif' => $totalActif,
            'total_passif' => $totalPassif
        ];
    }

    public function getResultatData($cabinetId, $clientId = null, $exerciceId = null)
    {
        $balances = $this->getBalancesByAccountCode($cabinetId, $clientId, $exerciceId);

        $charges = [
            'achats_marchandises' => $this->sumPrefix($balances, '601', 'debit'),
            'achats_matieres' => $this->sumPrefix($balances, '602', 'debit'),
            'autres_achats' => $this->sumPrefix($balances, ['604', '605', '608'], 'debit'),
            'services_exterieurs' => $this->sumPrefix($balances, ['61', '62', '63'], 'debit'),
            'impots' => $this->sumPrefix($balances, '64', 'debit'),
            'personnel' => $this->sumPrefix($balances, '66', 'debit'),
            'dotations' => $this->sumPrefix($balances, '68', 'debit'),
            'financieres' => $this->sumPrefix($balances, '67', 'debit'),
            'hao' => $this->sumPrefix($balances, '81', 'debit'),
        ];
        $totalCharges = array_sum($charges);

        $produits = [
            'ventes_marchandises' => $this->sumPrefix($balances, '701', 'credit'),
            'ventes_produits' => $this->sumPrefix($balances, ['702', '703', '704'], 'credit'),
            'services_vendus' => $this->sumPrefix($balances, '706', 'credit'),
            'accessoires' => $this->sumPrefix($balances, '707', 'credit'),
            'subventions' => $this->sumPrefix($balances, '71', 'credit'),
            'reprises' => $this->sumPrefix($balances, '79', 'credit'),
            'financiers' => $this->sumPrefix($balances, '77', 'credit'),
            'hao' => $this->sumPrefix($balances, '82', 'credit'),
        ];
        $totalProduits = array_sum($produits);

        return [
            'charges' => $charges,
            'produits' => $produits,
            'total_charges' => $totalCharges,
            'total_produits' => $totalProduits,
            'resultat_net' => $totalProduits - $totalCharges
        ];
    }

    public function getSigData($cabinetId, $clientId = null, $exerciceId = null)
    {
        $res = $this->getResultatData($cabinetId, $clientId, $exerciceId);
        $ch = $res['charges'];
        $pr = $res['produits'];

        $marge_commerciale = $pr['ventes_marchandises'] - $ch['achats_marchandises'];
        $valeur_ajoutee = $marge_commerciale + $pr['ventes_produits'] + $pr['services_vendus'] + $pr['accessoires'] - ($ch['achats_matieres'] + $ch['autres_achats'] + $ch['services_exterieurs']);
        $ebe = $valeur_ajoutee + $pr['subventions'] - $ch['impots'] - $ch['personnel'];
        $resultat_exploitation = $ebe + $pr['reprises'] - $ch['dotations'];
        $resultat_financier = $pr['financiers'] - $ch['financieres'];
        $rao = $resultat_exploitation + $resultat_financier;
        $rhao = $pr['hao'] - $ch['hao'];
        $resultat_net = $rao + $rhao;

        return [
            'marge_commerciale' => $marge_commerciale,
            'valeur_ajoutee' => $valeur_ajoutee,
            'ebe' => $ebe,
            'resultat_exploitation' => $resultat_exploitation,
            'resultat_financier' => $resultat_financier,
            'rao' => $rao,
            'rhao' => $rhao,
            'resultat_net' => $resultat_net
        ];
    }

    public function getTafireData($cabinetId, $clientId = null, $exerciceId = null)
    {
        // Simplification pour la démo
        $sig = $this->getSigData($cabinetId, $clientId, $exerciceId);
        $res = $this->getResultatData($cabinetId, $clientId, $exerciceId);
        
        $cafg = $sig['resultat_net'] + $res['charges']['dotations'] - $res['produits']['reprises'];

        $emplois = [
            'acquisitions' => 0, // Idéalement, variations de bilan
            'remboursements' => 0,
            'dividendes' => 0,
        ];
        
        $ressources = [
            'cafg' => $cafg,
            'cessions' => 0,
            'augmentation_capital' => 0,
            'emprunts' => 0,
        ];

        return [
            'emplois' => $emplois,
            'ressources' => $ressources,
            'total_emplois' => array_sum($emplois),
            'total_ressources' => array_sum($ressources)
        ];
    }

    public function getTresorerieData($cabinetId, $clientId = null, $exerciceId = null)
    {
        // Simplification pour la démo
        return [
            'activite_encaissements' => 0,
            'activite_decaissements' => 0,
            'investissement_acquisitions' => 0,
            'investissement_cessions' => 0,
            'financement_capitaux' => 0,
            'financement_emprunts' => 0,
            'financement_remboursements' => 0,
            'financement_dividendes' => 0,
            'tresorerie_nette' => 0
        ];
    }
}
