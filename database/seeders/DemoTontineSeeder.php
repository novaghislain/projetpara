<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Tontine;
use App\Models\TontineMembre;
use App\Models\TontineCotisation;
use Illuminate\Database\Seeder;

class DemoTontineSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::where('status', 'actif')->get();
        if ($clients->isEmpty()) return;

        $tontine = Tontine::create([
            'client_id'         => $clients->first()->id,
            'name'              => 'Tontine Épargne GEL',
            'type'              => 'epargne',
            'montant_cotisation'=> 50000,
            'periodicite'       => 'mensuel',
            'date_demarrage'    => now()->subMonths(3),
            'statut'            => 'actif',
        ]);

        foreach (range(1, 5) as $i) {
            $membre = TontineMembre::create([
                'tontine_id' => $tontine->id,
                'nom'        => fake()->name(),
                'telephone'  => fake()->phoneNumber(),
                'ordre_tour' => $i,
            ]);

            foreach (range(1, min(3, $i)) as $j) {
                TontineCotisation::create([
                    'tontine_id'   => $tontine->id,
                    'membre_id'    => $membre->id,
                    'periode'      => now()->subMonths(4 - $j)->format('Y-m'),
                    'montant'      => 50000,
                    'date_paiement'=> now()->subMonths(4 - $j),
                    'statut'       => 'payee',
                ]);
            }
        }

        $this->command->info('Tontine demo data seeded.');
    }
}
