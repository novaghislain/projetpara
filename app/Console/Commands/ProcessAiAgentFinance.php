<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\FiscalYear;
use App\Models\AiSuggestion;
use App\Services\IA\FinanceAiService;
use Illuminate\Console\Command;

class ProcessAiAgentFinance extends Command
{
    protected $signature = 'ai:agent-finance {--client-id=} {--fiscal-year-id=}';
    protected $description = 'Agent Finance : ratios, prévisions trésorerie, alertes';

    public function handle(FinanceAiService $service): int
    {
        $this->info('🚀 Agent Finance - Analyse financière...');

        $clientsQuery = Client::actif();
        if ($this->option('client-id')) {
            $clientsQuery->where('id', $this->option('client-id'));
        }
        $clients = $clientsQuery->get();

        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $total = 0;

        foreach ($clients as $client) {
            $fiscalYearId = $this->option('fiscal-year-id')
                ?: FiscalYear::where('client_id', $client->id)
                    ->where('status', 'open')
                    ->value('id');

            if (!$fiscalYearId) {
                $bar->advance();
                continue;
            }

            // 1. Analyse financière
            $analysis = $service->generateFinancialAnalysis($client->id, $fiscalYearId);

            // 2. Suggestion : rapport d'analyse
            $service->logLearning([
                'client_id' => $client->id,
                'type' => 'analyse_generee',
                'input_data' => json_encode(['client_id' => $client->id, 'fiscal_year_id' => $fiscalYearId]),
                'output_data' => json_encode($analysis),
            ]);

            // 3. Alertes
            $alerts = $service->detectAlerts($client->id, $fiscalYearId);

            foreach ($alerts as $alert) {
                AiSuggestion::create([
                    'client_id' => $client->id,
                    'agent' => 'finance',
                    'type' => 'alerte',
                    'title' => $alert['title'],
                    'description' => $alert['message'],
                    'data' => [
                        'severity' => $alert['severity'],
                        'detail' => $alert['detail'] ?? null,
                        'alert_type' => $alert['type'],
                    ],
                    'metadata' => [
                        'source' => 'finance_ai',
                        'generated_at' => now()->toDateTimeString(),
                    ],
                    'status' => 'pending',
                ]);
                $total++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Agent Finance terminé : {$total} alertes générées.");

        return Command::SUCCESS;
    }
}
