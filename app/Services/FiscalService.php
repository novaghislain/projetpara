<?php

namespace App\Services;

class FiscalService
{
    // TVA standard rate Bénin
    public const TVA_STANDARD = 18.0;

    // AIB rates by activity sector
    public const AIB_RATES = [
        'general'      => 1.0,  // BIC standard
        'trading'      => 1.0,  // Commerce
        'services'     => 1.5,  // Prestations de services
        'construction' => 2.0,  // BTP
        'imports'      => 5.0,  // Importations
    ];

    // CNSS rates
    public const CNSS_EMPLOYEE = 3.6;
    public const CNSS_EMPLOYER = 6.4;

    /**
     * Calculate TVA (18% unless overridden).
     */
    public static function calculateTva(float $amount, ?float $rate = null): array
    {
        $rate = $rate ?? self::TVA_STANDARD;
        $tvaAmount = round($amount * $rate / 100, 2);

        return [
            'base'     => $amount,
            'rate'     => $rate,
            'amount'   => $tvaAmount,
            'total'    => round($amount + $tvaAmount, 2),
        ];
    }

    /**
     * Calculate TVA from a gross (TTC) amount.
     */
    public static function extractTva(float $totalTtc, ?float $rate = null): array
    {
        $rate = $rate ?? self::TVA_STANDARD;
        $base = round($totalTtc / (1 + $rate / 100), 2);
        $tvaAmount = round($totalTtc - $base, 2);

        return [
            'base'     => $base,
            'rate'     => $rate,
            'amount'   => $tvaAmount,
            'total'    => $totalTtc,
        ];
    }

    /**
     * Calculate AIB (Acompte sur Impôt sur les Bénéfices).
     */
    public static function calculateAib(float $amount, string $sector = 'general'): array
    {
        $rate = self::AIB_RATES[$sector] ?? self::AIB_RATES['general'];
        $aibAmount = round($amount * $rate / 100, 2);

        return [
            'base'                => $amount,
            'rate'                => $rate,
            'amount'              => $aibAmount,
            'net_to_pay'          => round($amount + $aibAmount, 2),
        ];
    }

    /**
     * Calculate CNSS contributions for a salary.
     */
    public static function calculateCnss(float $grossSalary): array
    {
        return [
            'gross_salary'       => $grossSalary,
            'employee_share_pct' => self::CNSS_EMPLOYEE,
            'employee_share'     => round($grossSalary * self::CNSS_EMPLOYEE / 100, 2),
            'employer_share_pct' => self::CNSS_EMPLOYER,
            'employer_share'     => round($grossSalary * self::CNSS_EMPLOYER / 100, 2),
            'total_contribution' => round($grossSalary * (self::CNSS_EMPLOYEE + self::CNSS_EMPLOYER) / 100, 2),
        ];
    }

    /**
     * Validate IFU (Identifiant Fiscal Unique) format.
     * Format: 1234567890123 (13 digits) or with first 2 chars as letters
     */
    public static function validateIfu(?string $ifu): bool
    {
        if (empty($ifu)) return false;
        $ifu = trim($ifu);
        // Most common format: 13 digits
        if (preg_match('/^\d{13}$/', $ifu)) return true;
        // Alternative: first 2 chars letters + 11 digits
        if (preg_match('/^[A-Za-z]{2}\d{11}$/', $ifu)) return true;
        return false;
    }

    /**
     * Generate periodic TVA declaration data from journal lines.
     */
    public static function computeTvaDeclaration(
        iterable $journalLines,
        float $tvaRate = self::TVA_STANDARD
    ): array {
        $collected = 0;
        $deductible = 0;

        foreach ($journalLines as $line) {
            if (!empty($line->tva_code)) {
                if ($line->tva_type === 'collected' || $line->tva_type === 'output') {
                    $collected += $line->tva_amount ?? 0;
                } elseif ($line->tva_type === 'deductible' || $line->tva_type === 'input') {
                    $deductible += $line->tva_amount ?? 0;
                }
            }
        }

        return [
            'tva_collected'  => round($collected, 2),
            'tva_deductible' => round($deductible, 2),
            'tva_net'        => round($collected - $deductible, 2),
            'rate_applied'   => $tvaRate,
        ];
    }
}
