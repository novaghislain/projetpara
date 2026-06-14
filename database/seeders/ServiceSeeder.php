<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Comptabilité',
                'slug' => 'comptabilite',
                'description' => 'Tenue de comptabilité, bilan, déclarations fiscales, gestion de trésorerie',
                'icon' => 'bi-calculator',
                'color' => '#1a237e',
                'category' => 'finance',
            ],
            [
                'name' => 'Juridique',
                'slug' => 'juridique',
                'description' => 'Conseil juridique, rédaction de contrats, contentieux, droit des affaires',
                'icon' => 'bi-bank',
                'color' => '#c62828',
                'category' => 'juridique',
            ],
            [
                'name' => 'Fiscal',
                'slug' => 'fiscal',
                'description' => 'Optimisation fiscale, déclarations, veille réglementaire, TVA, IS',
                'icon' => 'bi-file-earmark-text',
                'color' => '#f57f17',
                'category' => 'finance',
            ],
            [
                'name' => 'Social',
                'slug' => 'social',
                'description' => 'Paie, déclarations sociales, CNSS, relations avec les organismes sociaux',
                'icon' => 'bi-people',
                'color' => '#2e7d32',
                'category' => 'rh',
            ],
            [
                'name' => 'Gestion Commerciale',
                'slug' => 'gestion-commerciale',
                'description' => 'Facturation, stocks, clients, trésorerie, gestion des ventes et achats',
                'icon' => 'bi-shop',
                'color' => '#3949ab',
                'category' => 'commercial',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['slug' => $service['slug']], $service);
        }
    }
}
