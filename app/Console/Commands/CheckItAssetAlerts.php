<?php

namespace App\Console\Commands;

use App\Models\ItAsset;
use App\Models\ItAssetLicense;
use Illuminate\Console\Command;

class CheckItAssetAlerts extends Command
{
    protected $signature = 'it:asset-alerts';
    protected $description = 'Vérifier les garanties et licences expirant dans les 30 jours';

    public function handle(): int
    {
        $this->info('Vérification des alertes IT...');

        $alerts = [];

        // 1. Garanties expirant dans 30 jours
        $garanties = ItAsset::whereNotNull('warranty_expires_at')
            ->whereBetween('warranty_expires_at', [now(), now()->addDays(30)])
            ->orderBy('warranty_expires_at')
            ->get();

        foreach ($garanties as $asset) {
            $expireDans = now()->diffInDays($asset->warranty_expires_at);
            $alerts[] = [
                'type'    => 'garantie',
                'asset'   => $asset->name,
                'client'  => $asset->client?->name ?? 'N/A',
                'date'    => $asset->warranty_expires_at->format('d/m/Y'),
                'message' => "Garantie de \"{$asset->name}\" expire dans {$expireDans} jours.",
            ];
            $this->warn("  [Garantie] {$asset->name} — expire le {$asset->warranty_expires_at->format('d/m/Y')}");
        }

        // 2. Licences expirant dans 30 jours
        $licences = ItAssetLicense::whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(30)])
            ->with('asset')
            ->orderBy('expires_at')
            ->get();

        foreach ($licences as $licence) {
            $expireDans = now()->diffInDays($licence->expires_at);
            $assetName = $licence->asset?->name ?? 'N/A';
            $alerts[] = [
                'type'    => 'licence',
                'asset'   => $assetName,
                'logiciel' => $licence->software_name,
                'date'    => $licence->expires_at->format('d/m/Y'),
                'message' => "Licence \"{$licence->software_name}\" sur {$assetName} expire dans {$expireDans} jours.",
            ];
            $this->warn("  [Licence] {$licence->software_name} ({$assetName}) — expire le {$licence->expires_at->format('d/m/Y')}");
        }

        if (empty($alerts)) {
            $this->info('  Aucune garantie ou licence expirant dans les 30 jours.');
        }

        $this->info('Vérification terminée. ' . count($alerts) . ' alerte(s) trouvée(s).');

        // On pourrait envoyer les alertes par email / notification ici

        return Command::SUCCESS;
    }
}
