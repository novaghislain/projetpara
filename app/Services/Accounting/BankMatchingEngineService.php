<?php

namespace App\Services\Accounting;

class BankMatchingEngineService
{
    /**
     * Rapprochement bancaire automatique selon des critères pondérés
     *
     * @param array $bankStatements Lignes du relevé bancaire
     * @param array $journalEntries Lignes d'écritures non lettrées (banque)
     * @return array
     */
    public function autoMatch(array $bankStatements, array $journalEntries): array
    {
        $matches = [];

        foreach ($bankStatements as $statement) {
            $bestMatch = null;
            $bestScore = 0;

            foreach ($journalEntries as $entry) {
                $score = $this->calculateScore($statement, $entry);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $entry;
                }
            }

            if ($bestScore >= 80) {
                $matches[] = [
                    'statement' => $statement,
                    'entry' => $bestMatch,
                    'score' => $bestScore,
                    'action' => 'MATCH_AUTOMATIQUE'
                ];
            } elseif ($bestScore >= 50) {
                $matches[] = [
                    'statement' => $statement,
                    'entry' => $bestMatch,
                    'score' => $bestScore,
                    'action' => 'SUGGESTION'
                ];
            } else {
                $matches[] = [
                    'statement' => $statement,
                    'entry' => null,
                    'score' => $bestScore,
                    'action' => 'MANUEL'
                ];
            }
        }

        return $matches;
    }

    private function calculateScore(array $statement, array $entry): int
    {
        $score = 0;

        // 1. Montant exact (Poids: 50)
        $statementAmount = (float) ($statement['amount'] ?? 0);
        $entryAmount = (float) ($entry['debit'] ?? 0) > 0 ? (float) $entry['debit'] : (float) ($entry['credit'] ?? 0);
        
        // Si sens inversé, ajustement (Dépense = débit compta, crédit relevé)
        // Simplification pour l'exemple
        if (abs($statementAmount) === abs($entryAmount)) {
            $score += 50;
        }

        // 2. Date à +/- 3 jours (Poids: 20)
        if (isset($statement['date']) && isset($entry['date'])) {
            $date1 = new \DateTime($statement['date']);
            $date2 = new \DateTime($entry['date']);
            $diff = $date1->diff($date2)->days;
            if ($diff <= 3) {
                $score += 20;
            }
        }

        // 3. Libellé similaire (Poids: 15)
        $libelleStatement = strtolower($statement['description'] ?? '');
        $libelleEntry = strtolower($entry['libelle'] ?? '');
        if ($libelleStatement && $libelleEntry && (str_contains($libelleStatement, $libelleEntry) || str_contains($libelleEntry, $libelleStatement))) {
            $score += 15;
        }

        // 4. Référence partagée (Poids: 10)
        $refStatement = strtolower($statement['reference'] ?? '');
        $refEntry = strtolower($entry['reference'] ?? '');
        if ($refStatement && $refEntry && $refStatement === $refEntry) {
            $score += 10;
        }

        // 5. Partenaire correspondant (Poids: 5)
        // Implémentation simplifiée
        
        return $score;
    }
}
