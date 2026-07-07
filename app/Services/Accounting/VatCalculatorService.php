<?php

namespace App\Services\Accounting;

class VatCalculatorService
{
    /**
     * Calcule la TVA pour une ligne donnée (OHADA / UEMOA)
     *
     * @param float $totalHt
     * @param float $tauxTva
     * @param float $discountPct
     * @param string $vatAccountId
     * @return array
     */
    public function calculateVatLine(float $totalHt, float $tauxTva, float $discountPct = 0, string $vatAccountId = '4411'): array
    {
        $base = $totalHt * (1 - ($discountPct / 100));
        $vatAmount = $base * ($tauxTva / 100);
        $totalTtc = $base + $vatAmount;

        return [
            'base' => round($base, 2),
            'vat_amount' => round($vatAmount, 2),
            'total_ttc' => round($totalTtc, 2),
            'vat_account' => $vatAccountId
        ];
    }

    /**
     * Génère la déclaration de TVA sur une période
     *
     * @param array $salesInvoices
     * @param array $purchaseInvoices
     * @param array $period ['month' => int, 'year' => int]
     * @param string $country
     * @return array
     */
    public function calculateVatDeclaration(array $salesInvoices, array $purchaseInvoices, array $period, string $country = 'BJ'): array
    {
        // Collectée (Ventes)
        $vatCollected = 0;
        $totalSalesHt = 0;

        foreach ($salesInvoices as $inv) {
            if ($inv['status'] === 'paid' || $inv['regime'] === 'debit') {
                $vatCollected += $inv['total_vat'];
                $totalSalesHt += $inv['total_ht'];
            }
        }

        // Déductible (Achats)
        $vatDeductible = 0;
        foreach ($purchaseInvoices as $inv) {
            if ($inv['status'] === 'paid' || $inv['regime'] === 'debit') {
                if (($inv['type'] ?? '') !== 'asset') {
                    $vatDeductible += $inv['total_vat'];
                }
            }
        }

        $vatDue = $vatCollected - $vatDeductible;

        return [
            'period' => $period,
            'country' => $country,
            'vat_collected' => round($vatCollected, 2),
            'total_sales_ht' => round($totalSalesHt, 2),
            'vat_deductible_goods' => round($vatDeductible, 2),
            'vat_deductible_assets' => 0,
            'vat_deductible_imports' => 0,
            'total_vat_deductible' => round($vatDeductible, 2),
            'vat_due' => $vatDue > 0 ? round($vatDue, 2) : 0,
            'vat_credit' => $vatDue < 0 ? round(abs($vatDue), 2) : 0,
            'status' => 'draft',
            'regime' => 'real_normal'
        ];
    }
}
