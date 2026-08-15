<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\Ai\RelanceAgentService;
use Illuminate\Console\Command;

class ProcessAiAgentRelance extends Command
{
    protected $signature = 'ai:agent-relance {--client-id=}';
    protected $description = 'Agent Relance : factures échues et règles de relance (CDC 13.4)';

    public function handle(RelanceAgentService $service): int
    {
        $this->info('📨 Agent Relance - Analyse des factures échues...');

        $clientsQuery = Client::actif();
        if ($this->option('client-id')) {
            $clientsQuery->where('id', $this->option('client-id'));
        }
        $clients = $clientsQuery->get();

        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $total = 0;

        foreach ($clients as $client) {
            // 1. Détection des factures en retard de paiement — 1 suggestion par lot
            $overdue = $service->analyzeOverdue($client->id);
            if (!empty($overdue['suggestion_id'])) {
                $total++;
            }

            // 2. Proposition de règles de relance si absentes — 1 suggestion
            $rules = $service->suggestRules($client->id);
            if (!empty($rules['suggestion_id'])) {
                $total++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Agent Relance terminé : {$total} suggestions générées.");

        return Command::SUCCESS;
    }
}
