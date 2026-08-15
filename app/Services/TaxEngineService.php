<?php

namespace App\Services;

class TaxEngineService
{
    /**
     * Taux d'AIB (Acompte sur Impôt Assis sur les Bénéfices) selon le CGI Bénin.
     */
    const AIB_RATE_WITH_IFU = 0.01; // 1%
    const AIB_RATE_WITHOUT_IFU = 0.05; // 5%

    /**
     * Calcule le montant de l'AIB à retenir à la source
     *
     * @param float $baseAmount Le montant hors taxes ou de base sur lequel s'applique l'AIB
     * @param bool $hasValidIfu Le prestataire a-t-il un IFU valide ?
     * @param bool $isExempt Le prestataire est-il exonéré d'AIB ?
     * @return float Le montant de l'AIB
     */
    public function calculateAIB(float $baseAmount, bool $hasValidIfu, bool $isExempt = false): float
    {
        if ($isExempt || $baseAmount <= 0) {
            return 0.0;
        }

        $rate = $hasValidIfu ? self::AIB_RATE_WITH_IFU : self::AIB_RATE_WITHOUT_IFU;

        return round($baseAmount * $rate, 2);
    }

    /**
     * Calcule la TVA selon le montant HT
     */
    public function calculateTVA(float $amountHT, float $tvaRate = 0.18): float
    {
        return round($amountHT * $tvaRate, 2);
    }

    /**
     * Compile tous les impôts d'une facture
     */
    public function compileInvoiceTaxes(float $amountHT, bool $hasValidIfu, bool $applyAIB = true, bool $applyTVA = true)
    {
        $tva = $applyTVA ? $this->calculateTVA($amountHT) : 0;
        $amountTTC = $amountHT + $tva;
        
        $aib = $applyAIB ? $this->calculateAIB($amountHT, $hasValidIfu) : 0;

        $netToPay = $amountTTC - $aib;

        return [
            'amount_ht' => $amountHT,
            'tva' => $tva,
            'amount_ttc' => $amountTTC,
            'aib' => $aib,
            'net_to_pay' => $netToPay,
            'aib_rate' => $hasValidIfu ? '1%' : '5%'
        ];
    }
}
