<?php

namespace Database\Seeders;

use App\Models\ErpBankAccount;
use Illuminate\Database\Seeder;

class ErpBankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Caisse Principale', 'type' => 'cash', 'account_number' => null, 'initial_balance' => 500000, 'is_active' => true],
            ['name' => 'Banque BOA', 'type' => 'bank', 'account_number' => 'BOA-001-2024-0001', 'initial_balance' => 5000000, 'is_active' => true],
            ['name' => 'Mobile Money MTN', 'type' => 'mobile_money', 'account_number' => '+229 97 00 00 01', 'initial_balance' => 200000, 'is_active' => true],
            ['name' => 'Caisse Projets', 'type' => 'cash', 'account_number' => null, 'initial_balance' => 0, 'is_active' => true],
        ];

        foreach ($accounts as $acct) {
            ErpBankAccount::updateOrCreate(['name' => $acct['name']], $acct);
        }
    }
}
