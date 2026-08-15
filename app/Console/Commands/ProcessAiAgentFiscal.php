<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\FiscalBeninService;
use Illuminate\Console\Command;

class ProcessAiAgentFiscal extends Command
{
    protected $signature = 'ai:agent-fiscal {--client-id=}';
    protected $description = 'Agent Fiscal : propositions TVA et alertes de déclaration (CDC 13.4)';

    public function handle(FiscalBeninService $service): int
    {
        $this->info('🧾 Agent Fiscal - Propositions TVA et alertes...');

        $clientsQuery = Client::actif();
        if ($this->option('client-id')) {
            $clientsQuery->where('id', $this->option('client-id'));
        }
        $clients = $clientsQuery->get();

        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $propositions = 0;
        $alertes = 0;

        foreach ($clients as $client) {
            $period = now()->format('Y-m');

            // 1. Proposition de déclaration de TVA (période courante) — 1 suggestion
            $tva = $service->proposeTvaDeclaration($client->id, $period);
            if (!empty($tva['suggestion_id'])) {
                $propositions++;
            }

            // 2. Alertes de déclaration (échéances, écritures de forte valeur, clôture)
            $alertes += count($service->generateAlerts($client->id));

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Agent Fiscal terminé : {$propositions} propositions TVA, {$alertes} alertes générées.");

        return Command::SUCCESS;
    }
}
