<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\CostCenter;
use Illuminate\Database\Seeder;

class DemoCostCenterSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::where('status', 'actif')->get();
        if ($clients->isEmpty()) return;

        $client = $clients->first();

        foreach ([
            ['code' => 'ADM', 'name' => 'Administration',      'type' => 'department'],
            ['code' => 'COM', 'name' => 'Comptabilité',        'type' => 'department'],
            ['code' => 'IT',  'name' => 'Informatique',        'type' => 'department'],
            ['code' => 'RH',  'name' => 'Ressources Humaines', 'type' => 'department'],
            ['code' => 'JUR', 'name' => 'Juridique',           'type' => 'department'],
        ] as $data) {
            CostCenter::create(['client_id' => $client->id] + $data);
        }

        $this->command->info('Cost centers demo data seeded.');
    }
}
