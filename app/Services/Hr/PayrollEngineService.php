<?php

namespace App\Services\Hr;

class PayrollEngineService
{
    /**
     * Taux CNSS (Caisse Nationale de Sécurité Sociale) au Bénin
     */
    const CNSS_EMPLOYEE_RATE = 0.036; // 3.6%
    const CNSS_EMPLOYER_RATE = 0.154; // 15.4% (Pensions 6.4%, Allocations 9%)
    const CNSS_CEILING = 500000; // Plafond mensuel pour certaines cotisations (ex: allocations familiales)

    /**
     * Taux VPS (Versement Patronal sur Salaires)
     */
    const VPS_RATE = 0.02; // 2% ou exonéré selon les cas (4% parfois, on simplifie à 2%)

    /**
     * Calcule la CNSS (Part salariale et patronale)
     */
    public function calculateCNSS(float $grossSalary): array
    {
        $cappedSalary = min($grossSalary, self::CNSS_CEILING);

        // La retraite (6.4% patronal + 3.6% salarial) n'est pas plafonnée au Bénin selon la nouvelle loi
        // Les allocations familiales (9% patronal) sont plafonnées à 500 000 FCFA
        $employeeShare = $grossSalary * self::CNSS_EMPLOYEE_RATE;
        $employerPensions = $grossSalary * 0.064;
        $employerAllocations = $cappedSalary * 0.09;
        $employerShare = $employerPensions + $employerAllocations;

        return [
            'employee_share' => round($employeeShare, 0),
            'employer_share' => round($employerShare, 0),
            'total_cnss' => round($employeeShare + $employerShare, 0),
        ];
    }

    /**
     * Calcule l'IPTS (Impôt sur le Revenu des Personnes Physiques / Traitements et Salaires)
     * selon le barème officiel du Bénin.
     */
    public function calculateIPTS(float $grossSalary, int $childrenCount = 0): float
    {
        // 1. Déduction CNSS
        $cnss = $this->calculateCNSS($grossSalary);
        $taxableSalary = $grossSalary - $cnss['employee_share'];

        // 2. Abattement forfaitaire de 20% pour frais professionnels
        // plafonné à un certain montant, simplifions à 20%
        $taxableSalaryAfterAbatement = $taxableSalary * 0.80;

        // 3. Barème progressif mensuel (Bénin)
        // - De 0 à 50 000 : 0%
        // - De 50 001 à 130 000 : 10%
        // - De 130 001 à 280 000 : 15%
        // - De 280 001 à 530 000 : 20%
        // - Au-delà de 530 000 : 30%
        
        $ipts = 0;
        $remaining = $taxableSalaryAfterAbatement;

        if ($remaining > 530000) {
            $ipts += ($remaining - 530000) * 0.30;
            $remaining = 530000;
        }
        if ($remaining > 280000) {
            $ipts += ($remaining - 280000) * 0.20;
            $remaining = 280000;
        }
        if ($remaining > 130000) {
            $ipts += ($remaining - 130000) * 0.15;
            $remaining = 130000;
        }
        if ($remaining > 50000) {
            $ipts += ($remaining - 50000) * 0.10;
        }

        // 4. Réduction pour charges de famille (enfants)
        // Ex: 1 enfant = 0%, 2 enfants = 5%, 3 enfants = 10%, 4 enfants = 15%, 5 enfants = 20%, 6+ = 23%
        $discountRate = match (true) {
            $childrenCount >= 6 => 0.23,
            $childrenCount === 5 => 0.20,
            $childrenCount === 4 => 0.15,
            $childrenCount === 3 => 0.10,
            $childrenCount === 2 => 0.05,
            default => 0.0,
        };

        $iptsAfterDiscount = $ipts * (1 - $discountRate);

        return max(0, round($iptsAfterDiscount, 0));
    }

    /**
     * Génère la fiche de paie complète
     */
    public function generatePayslip(float $grossSalary, int $childrenCount = 0): array
    {
        $cnss = $this->calculateCNSS($grossSalary);
        $ipts = $this->calculateIPTS($grossSalary, $childrenCount);
        $vps = round($grossSalary * self::VPS_RATE, 0);

        $totalDeductions = $cnss['employee_share'] + $ipts;
        $netSalary = $grossSalary - $totalDeductions;

        return [
            'gross_salary' => $grossSalary,
            'cnss_employee' => $cnss['employee_share'],
            'cnss_employer' => $cnss['employer_share'],
            'ipts' => $ipts,
            'vps' => $vps,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'employer_total_cost' => $grossSalary + $cnss['employer_share'] + $vps,
        ];
    }
}
