<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\JournalEntry;
use App\Models\AccountingAccount;
use App\Services\IA\AccountingAiService;
use Illuminate\Console\Command;

class ProcessAiAgentOhada extends Command
{
    protected $signature = 'ai:agent-ohada {--client-id= : Client spécifique à traiter}';
    protected $description = 'Agent OHADA : analyse les écritures non classifiées et détecte les anomalies';

    public function handle(AccountingAiService $service): int
    {
        $this->info('🚀 Agent OHADA - Démarrage de l\'analyse...');

        $clients = $this->option('client-id')
            ? Client::where('id', $this->option('client-id'))->get()
            : Client::where('is_active', true)->get();

        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $totalSuggestions = 0;

        foreach ($clients as $client) {
            // 1. Analyser les écritures non classifiées (sans suggestion AI existante)
            $unclassifiedEntries = JournalEntry::where('client_id', $client->id)
                ->whereIn('status', ['brouillon', 'draft'])
                ->whereNull('classified_by_ai')
                ->limit(50)
                ->get();

            foreach ($unclassifiedEntries as $entry) {
                $montant = max($entry->total_debit ?? 0, $entry->total_credit ?? 0);
                $type = $montant > 0 ? 'charge' : 'produit';

                $suggestions = $service->categorizeTransaction(
                    $entry->description ?? '',
                    abs($montant),
                    $type
                );

                $lines = $this->buildLinesFromSuggestions($suggestions, abs($montant));

                $service->createSuggestion($client->id, 1, [
                    'type' => 'suggestion',
                    'priority' => 'normal',
                    'title' => 'Catégorisation : ' . ($entry->description ?? 'Sans libellé'),
                    'message' => "L'écriture #{$entry->reference} du {$entry->entry_date->format('d/m/Y')} peut être classifiée : " .
                        $suggestions[0]['account_name'] . " (confiance: {$suggestions[0]['confidence']}%)",
                    'data' => [
                        'entry_id' => $entry->id,
                        'entry_reference' => $entry->reference,
                        'entry_date' => $entry->entry_date->format('Y-m-d'),
                        'entry_amount' => $montant,
                        'suggestions' => $suggestions,
                    ],
                    'confidence' => $suggestions[0]['confidence'],
                    'action_type' => 'categorize_transaction',
                    'action_payload' => [
                        'date' => $entry->entry_date->format('Y-m-d'),
                        'reference' => $entry->reference,
                        'description' => $entry->description,
                        'lines' => $lines,
                    ],
                ]);

                // Marquer l'écriture comme traitée par l'IA
                $entry->update(['classified_by_ai' => true]);
                $totalSuggestions++;
            }

            // 2. Détecter les anomalies
            $anomalies = $service->detectAnomalies(
                $client->id,
                now()->subMonths(3)->startOfMonth()->format('Y-m-d')
            );

            foreach ($anomalies as $anomaly) {
                $service->createSuggestion($client->id, 1, [
                    'type' => 'anomalie',
                    'priority' => $anomaly['severity'] === 'critical' ? 'critical' : 'high',
                    'title' => $anomaly['title'],
                    'message' => $anomaly['message'],
                    'data' => $anomaly,
                    'confidence' => 90,
                    'action_type' => 'flag_anomaly',
                ]);
                $totalSuggestions++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Agent OHADA terminé : {$totalSuggestions} suggestions créées.");

        return Command::SUCCESS;
    }

    private function buildLinesFromSuggestions(array $suggestions, float $amount): array
    {
        $lines = [];
        foreach ($suggestions as $sug) {
            $account = AccountingAccount::where('code', $sug['account_code'])->first();
            if (!$account) continue;

            $lines[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_label' => $account->name,
                'debit' => $sug['type'] === 'debit' ? $amount : 0,
                'credit' => $sug['type'] === 'credit' ? $amount : 0,
                'description' => $sug['reason'],
            ];
        }
        return $lines;
    }
}
