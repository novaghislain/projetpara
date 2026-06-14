<?php

namespace Database\Seeders;

use App\Models\Pole;
use Illuminate\Database\Seeder;

class PoleSeeder extends Seeder
{
    public function run(): void
    {
        $poles = [
            ['name' => 'Comptabilité', 'slug' => 'comptabilite', 'description' => 'Gestion comptable, bilan, déclarations fiscales', 'color' => '#1a237e', 'is_active' => true],
            ['name' => 'Juridique', 'slug' => 'juridique', 'description' => 'Conseil juridique, rédaction de contrats, contentieux', 'color' => '#c62828', 'is_active' => true],
            ['name' => 'Fiscal', 'slug' => 'fiscal', 'description' => 'Optimisation fiscale, déclarations, veille réglementaire', 'color' => '#f57f17', 'is_active' => true],
            ['name' => 'Social', 'slug' => 'social', 'description' => 'Paie, déclarations sociales, relations avec les organismes', 'color' => '#2e7d32', 'is_active' => true],
            ['name' => 'Informatique', 'slug' => 'informatique', 'description' => 'Support IT, développement, infrastructure et maintenance', 'color' => '#6a1b9a', 'is_active' => true],
        ];

        foreach ($poles as $pole) {
            Pole::updateOrCreate(['slug' => $pole['slug']], $pole);
        }
    }
}
