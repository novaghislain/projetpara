<?php

namespace App\Services;

use App\Models\AiSuggestion;
use App\Models\AiLearningLog;
use App\Models\AccountingJournalLine;
use App\Models\TvaDeclaration;
use App\Models\FiscalYear;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class FiscalBeninService
{
    /**
     * Analyse les écritures comptables pour pré-remplir une déclaration de TVA.
     * Génère des suggestions IA à valider par l'utilisateur.
     */
    public function proposeTvaDeclaration(int $clientId, string $period, ?int $fiscalYearId = null): array
    {
        // Période : YYYY-MM
        [$year, $month] = explode('-', $period);
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Trouver ou créer l'exercice fiscal
        if (!$fiscalYearId) {
            $fiscalYear = FiscalYear::firstOrCreate(
                ['client_id' => $clientId, 'year' => $year],
                ['start_date' => "{$year}-01-01", 'end_date' => "{$year}-12-31", 'status' => 'open']
            );
            $fiscalYearId = $fiscalYear->id;
        }

        // Analyser les lignes de journal de la période
        $lines = AccountingJournalLine::whereHas('journal', function ($q) use ($clientId, $startDate, $endDate) {
            $q->where('client_id', $clientId)
              ->whereBetween('date', [$startDate, $endDate]);
        })->get();

        $tvaCollected = 0;
        $tvaDeductible = 0;
        $collectedDetail = [];
        $deductibleDetail = [];

        foreach ($lines as $line) {
            $tvaAmount = $line->tva_amount ?? 0;
            if ($tvaAmount <= 0) continue;

            $accountCode = $line->account_code ?? '';

            // TVA collectée (comptes 44x ou comptes de vente)
            if (str_starts_with($accountCode, '443') || $line->tva_type === 'collected' || $line->tva_type === 'output') {
                $tvaCollected += $tvaAmount;
                $collectedDetail[] = [
                    'line_id' => $line->id,
                    'account' => $accountCode,
                    'amount' => $tvaAmount,
                    'label' => $line->label ?? 'Vente',
                    'date' => $line->date,
                ];
            }
            // TVA déductible (comptes 44566)
            elseif (str_starts_with($accountCode, '4456') || $line->tva_type === 'deductible' || $line->tva_type === 'input') {
                $tvaDeductible += $tvaAmount;
                $deductibleDetail[] = [
                    'line_id' => $line->id,
                    'account' => $accountCode,
                    'amount' => $tvaAmount,
                    'label' => $line->label ?? 'Achat',
                    'date' => $line->date,
                ];
            }
        }

        $tvaNet = round($tvaCollected - $tvaDeductible, 2);

        // Créer une suggestion IA
        $suggestion = AiSuggestion::create([
            'client_id' => $clientId,
            'agent' => 'fiscal',
            'type' => 'tva_declaration',
            'title' => "Déclaration TVA — {$period}",
            'description' => "TVA collectée: " . number_format($tvaCollected, 0, ',', ' ') .
                             " FCFA | TVA déductible: " . number_format($tvaDeductible, 0, ',', ' ') .
                             " FCFA | Net à payer: " . number_format($tvaNet, 0, ',', ' ') . " FCFA",
            'data' => [
                'period' => $period,
                'fiscal_year_id' => $fiscalYearId,
                'tva_collected' => $tvaCollected,
                'tva_deductible' => $tvaDeductible,
                'tva_net' => $tvaNet,
                'collected_detail' => $collectedDetail,
                'deductible_detail' => $deductibleDetail,
                'line_count' => count($lines),
            ],
            'metadata' => [
                'agent' => 'Fiscal Bénin',
                'version' => '1.0',
                'auto_generated' => true,
            ],
            'status' => 'pending',
        ]);

        // Journal d'apprentissage
        AiLearningLog::create([
            'client_id' => $clientId,
            'agent' => 'fiscal',
            'type' => 'tva_proposal',
            'input_data' => "Période: {$period}, client_id: {$clientId}",
            'output_data' => json_encode([
                'tva_collected' => $tvaCollected,
                'tva_deductible' => $tvaDeductible,
                'tva_net' => $tvaNet,
                'line_count' => count($lines),
            ]),
        ]);

        return [
            'suggestion_id' => $suggestion->id,
            'period' => $period,
            'tva_collected' => $tvaCollected,
            'tva_deductible' => $tvaDeductible,
            'tva_net' => $tvaNet,
            'collected_count' => count($collectedDetail),
            'deductible_count' => count($deductibleDetail),
            'status' => 'pending',
        ];
    }

    /**
     * Génère des alertes fiscales pour un client.
     */
    public function generateAlerts(int $clientId): array
    {
        $alerts = [];
        $now = now();
        $currentMonth = $now->format('Y-m');

        // 1. Alerte échéance TVA (le 15 du mois suivant)
        $declared = TvaDeclaration::where('client_id', $clientId)
            ->where('period', $currentMonth)
            ->exists();

        if (!$declared) {
            $dueDate = date('Y-m-15', strtotime('+1 month'));
            $daysLeft = (strtotime($dueDate) - time()) / 86400;

            if ($daysLeft <= 15) {
                $urgency = $daysLeft <= 3 ? 'urgent' : 'warning';
                $alerts[] = [
                    'type' => 'tva_due',
                    'title' => 'Déclaration TVA à soumettre',
                    'description' => "La déclaration TVA pour {$currentMonth} est à soumettre avant le {$dueDate} (J-{$daysLeft}).",
                    'urgency' => $urgency,
                    'due_date' => $dueDate,
                ];
            }
        }

        // 2. Vérification d'anomalies comptables
        $hightValueLines = AccountingJournalLine::whereHas('journal', function ($q) use ($clientId) {
            $q->where('client_id', $clientId)
              ->where('date', '>=', now()->subMonth(3));
        })->where('debit', '>=', 5000000)
          ->orWhere('credit', '>=', 5000000)
          ->limit(5)
          ->get();

        if ($hightValueLines->count() > 0) {
            $alerts[] = [
                'type' => 'high_value',
                'title' => 'Écritures de valeur élevée détectées',
                'description' => $hightValueLines->count() . ' écritures ≥ 5M FCFA sur les 3 derniers mois.',
                'urgency' => 'info',
            ];
        }

        // 3. Suggestion de clôture d'exercice
        $openYear = FiscalYear::where('client_id', $clientId)
            ->where('status', 'open')
            ->where('end_date', '<=', $now)
            ->first();

        if ($openYear) {
            $alerts[] = [
                'type' => 'closing_due',
                'title' => 'Clôture d\'exercice en attente',
                'description' => "L'exercice {$openYear->year} est terminé depuis le {$openYear->end_date->format('d/m/Y')}.",
                'urgency' => 'warning',
                'fiscal_year_id' => $openYear->id,
            ];
        }

        // Créer des suggestions IA pour les alertes
        foreach ($alerts as $alert) {
            AiSuggestion::create([
                'client_id' => $clientId,
                'agent' => 'fiscal',
                'type' => 'alert_' . $alert['type'],
                'title' => $alert['title'],
                'description' => $alert['description'],
                'data' => $alert,
                'metadata' => ['agent' => 'Fiscal Bénin', 'urgency' => $alert['urgency']],
                'status' => 'pending',
            ]);
        }

        return $alerts;
    }

    /**
     * Applique une suggestion de déclaration TVA (crée la déclaration réelle).
     */
    public function applyTvaSuggestion(AiSuggestion $suggestion): ?TvaDeclaration
    {
        if ($suggestion->agent !== 'fiscal' || $suggestion->status !== 'approved') {
            return null;
        }

        $data = $suggestion->data;
        if (!$data || !isset($data['period'])) {
            return null;
        }

        $declaration = TvaDeclaration::create([
            'client_id' => $suggestion->client_id,
            'period' => $data['period'],
            'fiscal_year_id' => $data['fiscal_year_id'] ?? null,
            'type' => 'monthly',
            'tva_collected' => $data['tva_collected'] ?? 0,
            'tva_deductible' => $data['tva_deductible'] ?? 0,
            'tva_net' => $data['tva_net'] ?? 0,
            'details' => $data,
            'status' => 'draft',
            'created_by' => $suggestion->approved_by,
        ]);

        $suggestion->update(['status' => 'applied']);

        return $declaration;
    }
}
