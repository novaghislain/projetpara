<?php

namespace App\Console\Commands;

use App\Models\DunningCampaign;
use App\Services\RelanceIntelligenteService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessDunningCampaigns extends Command
{
    protected $signature = 'dunning:process';
    protected $description = 'Exécute l\'agent de relance IA sur les campagnes actives';

    public function handle(RelanceIntelligenteService $relanceService)
    {
        $this->info('🤖 Agent Relance IA — Traitement des campagnes...');

        $campaigns = DunningCampaign::where('status', 'active')
            ->where(function($query) {
                $query->whereNull('next_action_at')
                      ->orWhere('next_action_at', '<=', Carbon::now());
            })
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('Aucune relance à effectuer aujourd\'hui.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($campaigns->count());
        $bar->start();

        foreach ($campaigns as $campaign) {
            $relanceService->processCampaign($campaign);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ ' . $campaigns->count() . ' campagnes traitées par l\'IA.');

        return Command::SUCCESS;
    }
}
