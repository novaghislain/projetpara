<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\IA\CustomerAiService;
use Illuminate\Console\Command;

class ProcessAiAgentCustomer extends Command
{
    protected $signature = 'ai:agent-customer {--client-id= : Client spécifique à analyser}';
    protected $description = 'Agent Customer : score, relances, cross-sell, churn';

    public function handle(CustomerAiService $service): int
    {
        $this->info('🚀 Agent Customer - Analyse des clients...');

        $clients = $this->option('client-id')
            ? Client::where('id', $this->option('client-id'))->get()
            : Client::actif()->get();

        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $totalSuggestions = 0;

        foreach ($clients as $client) {
            // 1. Score de lead
            $scoring = $service->calculateLeadScore($client);
            $client->update(['score' => $scoring['score']]);

            // 2. Actions de relance
            $actions = $service->suggestFollowUpActions($client);
            foreach ($actions as $action) {
                $service->createSuggestion($client->id, 1, [
                    'type' => 'action',
                    'priority' => $action['priority'] === 'haute' ? 'high' : 'normal',
                    'title' => $action['title'],
                    'message' => $action['message'],
                    'data' => $action,
                    'confidence' => 85,
                    'action_type' => 'follow_up',
                    'action_payload' => [
                        'channel' => $action['channel'],
                        'script' => $action['script'],
                    ],
                ]);
                $totalSuggestions++;
            }

            // 3. Opportunités cross-sell
            $opportunities = $service->detectCrossSellOpportunities($client);
            foreach ($opportunities as $opp) {
                $service->createSuggestion($client->id, 1, [
                    'type' => 'opportunite',
                    'priority' => $opp['priority'] === 'haute' ? 'high' : 'normal',
                    'title' => $opp['title'],
                    'message' => $opp['message'],
                    'data' => $opp,
                    'confidence' => 75,
                    'action_type' => 'cross_sell',
                ]);
                $totalSuggestions++;
            }

            // 4. Risque de churn
            $churn = $service->predictChurnRisk($client);
            if ($churn['risk_level'] !== 'faible') {
                $service->createSuggestion($client->id, 1, [
                    'type' => 'alerte',
                    'priority' => $churn['risk_level'] === 'critique' ? 'critical' : 'high',
                    'title' => 'Risque de perte client : ' . $churn['risk_level'],
                    'message' => $churn['recommendation'],
                    'data' => $churn,
                    'confidence' => 80,
                    'action_type' => 'churn_prevention',
                ]);
                $totalSuggestions++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Agent Customer terminé : {$totalSuggestions} actions suggérées.");

        return Command::SUCCESS;
    }
}
