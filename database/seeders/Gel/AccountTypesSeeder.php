<?php

namespace Database\Seeders\Gel;

use App\Models\Gel\AccountType;
use Illuminate\Database\Seeder;

class AccountTypesSeeder extends Seeder
{
    public function run(): void
    {
        AccountType::firstOrCreate(['code' => 'cabinet'], [
            'libelle' => 'Cabinet Comptable',
            'description' => 'Compte pour les cabinets comptables (experts-comptables, collaborateurs)',
            'actif' => true,
        ]);

        AccountType::firstOrCreate(['code' => 'entreprise'], [
            'libelle' => 'Entreprise / Client',
            'description' => 'Compte pour les entreprises clientes du cabinet',
            'actif' => true,
        ]);
    }
}
