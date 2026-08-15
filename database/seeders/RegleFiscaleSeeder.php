<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RegleFiscale;
use Illuminate\Support\Str;

class RegleFiscaleSeeder extends Seeder
{
    public function run()
    {
        // Vider la table pour éviter les doublons lors des tests
        RegleFiscale::truncate();

        // 1. TVA 18% par défaut (avec tax_group = B pour e-MECeF)
        RegleFiscale::create([
            'id' => Str::uuid(),
            'code_pays' => 'BJ',
            'type_impot' => 'TVA',
            'taux' => 0.1800,
            'conditions' => null,
            'date_debut_validite' => '2000-01-01',
            'version' => 1,
            'statut' => 'active',
            'source_reglementaire' => 'CGI Bénin - Code Général des Impôts',
        ]);

        // 2. AIB 1% pour les entreprises rattachées à la DGE ou DME
        RegleFiscale::create([
            'id' => Str::uuid(),
            'code_pays' => 'BJ',
            'type_impot' => 'AIB',
            'taux' => 0.0100,
            'conditions' => ['centre_impots' => 'DGE_DME'],
            'date_debut_validite' => '2000-01-01',
            'version' => 1,
            'statut' => 'active',
            'source_reglementaire' => 'CGI Bénin - AIB DGE/DME',
        ]);

        // 3. AIB 5% pour les entreprises rattachées aux CSI
        RegleFiscale::create([
            'id' => Str::uuid(),
            'code_pays' => 'BJ',
            'type_impot' => 'AIB',
            'taux' => 0.0500,
            'conditions' => ['centre_impots' => 'CSI'],
            'date_debut_validite' => '2000-01-01',
            'version' => 1,
            'statut' => 'active',
            'source_reglementaire' => 'CGI Bénin - AIB CSI',
        ]);
    }
}
