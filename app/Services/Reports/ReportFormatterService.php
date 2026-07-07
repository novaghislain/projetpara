<?php
namespace App\Services\Reports;

class ReportFormatterService
{
    /**
     * Formate un montant en XAF/FCFA
     */
    public static function formatAmount(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Formate un montant décimal
     */
    public static function formatDecimal(float $amount, int $decimals = 2): string
    {
        return number_format($amount, $decimals, ',', ' ');
    }

    /**
     * Retourne la nature du solde (débiteur/créditeur)
     */
    public static function getBalanceNature(string $accountType, ?string $accountNature = null): string
    {
        if ($accountNature === 'debit') return 'Débiteur';
        if ($accountNature === 'credit') return 'Créditeur';

        return match ($accountType) {
            'asset', 'expense' => 'Débiteur',
            'liability', 'equity', 'revenue' => 'Créditeur',
            default => 'Débiteur/Créditeur',
        };
    }

    /**
     * Intitulés SYSCOHADA pour les classes
     */
    public static function getClassLabel(string $classCode): string
    {
        return match ($classCode) {
            '1' => 'Capitaux permanents',
            '2' => 'Actif immobilisé',
            '3' => 'Stocks',
            '4' => 'Tiers',
            '5' => 'Trésorerie',
            '6' => 'Charges',
            '7' => 'Produits',
            '8' => 'Engagements hors bilan',
            default => 'Classe ' . $classCode,
        };
    }

    /**
     * Intitulés SYSCOHADA pour les types de comptes
     */
    public static function getTypeLabel(string $type): string
    {
        return match ($type) {
            'asset' => 'Actif',
            'liability' => 'Passif',
            'equity' => 'Capitaux propres',
            'revenue' => 'Produits',
            'expense' => 'Charges',
            'contra_asset' => 'Amortissements',
            'contra_liability' => 'Provisions',
            default => $type,
        };
    }

    /**
     * Traduit un numéro de mois en français
     */
    public static function getMonthLabel(int $month): string
    {
        return match ($month) {
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars',
            4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
            7 => 'Juillet', 8 => 'Août', 9 => 'Septembre',
            10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
            default => 'Mois ' . $month,
        };
    }

    /**
     * Retourne la devise par défaut (XOF/XAF)
     */
    public static function getDefaultCurrency(): string
    {
        return 'FCFA';
    }

    /**
     * Arrondit un montant selon les règles SYSCOHADA
     */
    public static function syscohadaRound(float $amount): float
    {
        return round($amount, 2);
    }
}
