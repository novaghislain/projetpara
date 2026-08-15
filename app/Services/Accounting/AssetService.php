<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Asset;
use App\Models\Accounting\AssetDepreciation;
use Carbon\Carbon;

class AssetService
{
    /**
     * Génère le tableau d'amortissement linéaire pour une immobilisation.
     */
    public function generateLinearDepreciation(Asset $asset)
    {
        // Supprimer les anciens calculs si l'actif n'a pas encore d'écritures comptabilisées
        $asset->depreciations()->where('is_posted', false)->delete();

        $purchasePrice = (float) $asset->purchase_price;
        $salvageValue = (float) $asset->salvage_value;
        $usefulLife = (int) $asset->useful_life_years;

        if ($usefulLife <= 0) {
            return collect();
        }

        $depreciableBasis = $purchasePrice - $salvageValue;
        
        // Amortissement annuel théorique complet
        $annualDepreciation = $depreciableBasis / $usefulLife;

        $purchaseDate = Carbon::parse($asset->purchase_date);
        $currentYear = $purchaseDate->year;

        // Calcul du prorata temporis pour la première année
        // Base : 360 jours par an, 30 jours par mois
        // Simplification : Jours restants dans l'année / 360
        $endOfYear = Carbon::createFromDate($currentYear, 12, 31);
        $daysInFirstYear = $purchaseDate->diffInDays($endOfYear) + 1; // +1 pour inclure le jour d'achat
        // OHADA simplifie souvent en considérant des mois entiers si acheté le 1er. On utilise un calcul direct de ratio :
        $firstYearRatio = min(1, $daysInFirstYear / 365); // Utilisons 365 pour plus de précision standard ou 360 selon le paramétrage comptable

        $accumulated = 0;
        $bookValue = $purchasePrice;

        $depreciations = [];

        // Première année
        $firstYearAmount = round($annualDepreciation * $firstYearRatio, 2);
        if ($firstYearAmount > 0) {
            $accumulated += $firstYearAmount;
            $bookValue -= $firstYearAmount;

            $depreciations[] = AssetDepreciation::create([
                'asset_id' => $asset->id,
                'depreciation_date' => $endOfYear->format('Y-m-d'),
                'depreciation_amount' => $firstYearAmount,
                'accumulated_depreciation' => $accumulated,
                'book_value' => round($bookValue, 2)
            ]);
        }

        // Années intermédiaires
        for ($i = 1; $i < $usefulLife; $i++) {
            $currentYear++;
            $amount = round($annualDepreciation, 2);
            $accumulated += $amount;
            $bookValue -= $amount;

            $depreciations[] = AssetDepreciation::create([
                'asset_id' => $asset->id,
                'depreciation_date' => Carbon::createFromDate($currentYear, 12, 31)->format('Y-m-d'),
                'depreciation_amount' => $amount,
                'accumulated_depreciation' => $accumulated,
                'book_value' => round($bookValue, 2)
            ]);
        }

        // Dernière année (Solde / reliquat du prorata)
        if ($bookValue > $salvageValue) {
            $currentYear++;
            $finalAmount = round($bookValue - $salvageValue, 2);
            $accumulated += $finalAmount;
            $bookValue -= $finalAmount;

            $depreciations[] = AssetDepreciation::create([
                'asset_id' => $asset->id,
                'depreciation_date' => Carbon::createFromDate($currentYear, 12, 31)->format('Y-m-d'),
                'depreciation_amount' => $finalAmount,
                'accumulated_depreciation' => $accumulated,
                'book_value' => round($bookValue, 2)
            ]);
        }

        return collect($depreciations);
    }
}
