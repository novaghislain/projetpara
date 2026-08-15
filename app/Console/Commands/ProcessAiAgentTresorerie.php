<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\Ai\CashflowAgentService;
use Illuminate\Console\Command;

class ProcessAiAgentTresorerie extends Command
{
    protected $signature = 'ai:agent-tresorerie {--client-id=}';
    protected $description = 'Agent Trésorerie : prévisions de trésorerie sur 3 mois (CDC 13.4)';

    public function handle(CashflowAgentService $service): int
    {
        $this->info('💰 Agent Trésorerie - Prévisions de trésorerie...');

        $clientsQuery = Client::actif();
        if ($this->option('client-id')) {
            $clientsQuery->where('id', $this->option('client-id'));
        }
        $clients = $clientsQuery->get();

        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $total = 0;

        foreach ($clients as $client) {
            // Prévision de trésorerie sur 3 mois — 1 suggestion par lot
            $result = $service->forecast($client->id, 3);
            if (!empty($result['suggestion_id'])) {
                $total++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Agent Trésorerie terminé : {$total} prévision(s) générée(s).");

        return Command::SUCCESS;
    }
}
