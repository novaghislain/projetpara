<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\Ai\ReconciliationAgentService;
use Illuminate\Console\Command;

class ProcessAiAgentRapprochement extends Command
{
    protected $signature = 'ai:agent-rapprochement {--client-id=}';
    protected $description = 'Agent Rapprochement : écarts significatifs et lettrage automatique (CDC 13.4)';

    public function handle(ReconciliationAgentService $service): int
    {
        $this->info('🏦 Agent Rapprochement - Analyse des écarts et lettrage...');

        $clientsQuery = Client::actif();
        if ($this->option('client-id')) {
            $clientsQuery->where('id', $this->option('client-id'));
        }
        $clients = $clientsQuery->get();

        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $ecarts = 0;
        $lettrage = 0;

        foreach ($clients as $client) {
            // 1. Écarts significatifs (> 1 000 FCFA) — 1 suggestion par lot
            $rec = $service->analyze($client->id);
            if (!empty($rec['suggestion_id'])) {
                $ecarts++;
            }

            // 2. Suggestion de lettrage des comptes 401/411 — 1 suggestion
            $match = $service->suggestMatching($client->id);
            if (!empty($match['suggestion_id'])) {
                $lettrage++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Agent Rapprochement terminé : {$ecarts} écart(s) signalé(s), {$lettrage} suggestion(s) de lettrage.");

        return Command::SUCCESS;
    }
}
