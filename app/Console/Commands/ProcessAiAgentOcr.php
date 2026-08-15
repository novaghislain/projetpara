<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\Ai\OcrAgentService;
use Illuminate\Console\Command;

class ProcessAiAgentOcr extends Command
{
    protected $signature = 'ai:agent-ocr {--client-id=}';
    protected $description = 'Agent OCR : documents à numériser et classification (CDC 13.4)';

    public function handle(OcrAgentService $service): int
    {
        $this->info('📄 Agent OCR - Analyse des documents à numériser...');

        $clientsQuery = Client::actif();
        if ($this->option('client-id')) {
            $clientsQuery->where('id', $this->option('client-id'));
        }
        $clients = $clientsQuery->get();

        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $total = 0;

        foreach ($clients as $client) {
            // Documents non traités (sans hash) — 1 suggestion par lot
            $result = $service->suggestOcr($client->id);
            if (!empty($result['suggestion_id'])) {
                $total++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Agent OCR terminé : {$total} suggestion(s) de numérisation générée(s).");

        return Command::SUCCESS;
    }
}
