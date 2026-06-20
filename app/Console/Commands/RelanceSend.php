<?php

namespace App\Console\Commands;

use App\Services\RelanceService;
use Illuminate\Console\Command;

class RelanceSend extends Command
{
    protected $signature = 'relance:send';
    protected $description = 'Envoyer les relances automatiques pour les factures échues';

    public function handle(RelanceService $relanceService): int
    {
        $this->info('Démarrage des relances automatiques...');

        $sent = $relanceService->executeAll();

        $this->info("$sent relances envoyées.");

        return Command::SUCCESS;
    }
}
