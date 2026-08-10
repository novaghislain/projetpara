<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gel\RegleFiscale;
use Carbon\Carbon;

class FiscaliteBeninSeeder extends Seeder
{
    public function run(): void
    {
        $dateEffet = Carbon::create(2026, 1, 1);

        $regles = [
            [
                'pays_code' => 'BJ',
                'type_impot' => 'IS',
                'nom' => 'Impôt sur les Sociétés (IS)',
                'taux' => 30.00,
                'bareme' => null,
                'description' => 'Taux normal de l\'impôt sur les sociétés au Bénin',
                'date_effet' => $dateEffet
            ],
            [
                'pays_code' => 'BJ',
                'type_impot' => 'TVA',
                'nom' => 'Taxe sur la Valeur Ajoutée (TVA)',
                'taux' => 18.00,
                'bareme' => null,
                'description' => 'Taux unique de la TVA',
                'date_effet' => $dateEffet
            ],
            [
                'pays_code' => 'BJ',
                'type_impot' => 'VPS',
                'nom' => 'Versement Patronal sur Salaires (VPS)',
                'taux' => 4.00, // Or 2% if educational, 4% is standard
                'bareme' => null,
                'description' => 'Taux de droit commun du VPS',
                'date_effet' => $dateEffet
            ],
            [
                'pays_code' => 'BJ',
                'type_impot' => 'ITS',
                'nom' => 'Impôt sur les Traitements et Salaires (ITS)',
                'taux' => null,
                'bareme' => json_encode([
                    ['min' => 0, 'max' => 50000, 'taux' => 0],
                    ['min' => 50001, 'max' => 130000, 'taux' => 10],
                    ['min' => 130001, 'max' => 280000, 'taux' => 15],
                    ['min' => 280001, 'max' => 530000, 'taux' => 20],
                    ['min' => 530001, 'max' => null, 'taux' => 30]
                ]),
                'description' => 'Barème progressif de l\'ITS mensuel',
                'date_effet' => $dateEffet
            ],
            [
                'pays_code' => 'BJ',
                'type_impot' => 'TPS',
                'nom' => 'Taxe Professionnelle Synthétique (TPS)',
                'taux' => null,
                'bareme' => json_encode([
                    'prestation_services' => 12,
                    'commerce' => 5,
                    'minimum_forfaitaire' => 10000
                ]),
                'description' => 'TPS pour les micro-entreprises',
                'date_effet' => $dateEffet
            ]
        ];

        foreach ($regles as $regle) {
            RegleFiscale::updateOrCreate(
                [
                    'pays_code' => $regle['pays_code'],
                    'type_impot' => $regle['type_impot']
                ],
                $regle
            );
        }
    }
}
