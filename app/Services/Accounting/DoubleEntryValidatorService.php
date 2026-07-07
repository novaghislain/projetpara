<?php

namespace App\Services\Accounting;

class DoubleEntryValidatorService
{
    /**
     * Valide le principe de la partie double (Débits = Crédits)
     *
     * @param array $lines
     * @return bool
     * @throws \Exception
     */
    public function validate(array $lines): bool
    {
        if (count($lines) < 2) {
            throw new \Exception("Une écriture doit contenir au minimum 2 lignes.");
        }

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $line) {
            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        // Utilisation de bccomp pour éviter les problèmes de flottants
        if (bccomp((string) $totalDebit, (string) $totalCredit, 2) !== 0) {
            throw new \Exception("Déséquilibre détecté : Total Débit ({$totalDebit}) != Total Crédit ({$totalCredit}).");
        }

        return true;
    }
}
