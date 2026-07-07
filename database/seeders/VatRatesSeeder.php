<?php

namespace Database\Seeders;

use App\Models\VatRate;
use App\Models\AccountingAccount;
use App\Models\Client;
use Illuminate\Database\Seeder;

class VatRatesSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();

        if ($clients->isEmpty()) {
            $this->command->warn('⚠️  Aucun client trouvé. Les taux TVA ne seront créés que si un client existe.');
            return;
        }

        foreach ($clients as $client) {
            // Compte TVA collectée SYSCOHADA (40211) ou fallback compte 443
            $collectAccount = AccountingAccount::where('client_id', $client->id)
                ->where('code', '40211')
                ->orWhere(function ($q) use ($client) {
                    $q->where('client_id', 0)->where('code', '443');
                })
                ->first();

            // Compte TVA déductible SYSCOHADA (3122) ou fallback compte 445
            $deductAccount = AccountingAccount::where('client_id', $client->id)
                ->where('code', '3122')
                ->orWhere(function ($q) use ($client) {
                    $q->where('client_id', 0)->where('code', '445');
                })
                ->first();

            $rates = [
                [
                    'code' => 'T',
                    'name' => 'TVA Normale (18%)',
                    'rate' => 18.00,
                    'type' => 'standard',
                    'is_default' => true,
                    'collect_account_id' => $collectAccount?->id,
                    'deduct_account_id' => $deductAccount?->id,
                ],
                [
                    'code' => 'R',
                    'name' => 'TVA Réduite (9%)',
                    'rate' => 9.00,
                    'type' => 'reduced',
                    'is_default' => false,
                    'collect_account_id' => $collectAccount?->id,
                    'deduct_account_id' => $deductAccount?->id,
                ],
                [
                    'code' => 'S',
                    'name' => 'TVA Super Réduite (5%)',
                    'rate' => 5.00,
                    'type' => 'super_reduced',
                    'is_default' => false,
                    'collect_account_id' => $collectAccount?->id,
                    'deduct_account_id' => $deductAccount?->id,
                ],
                [
                    'code' => 'N',
                    'name' => 'Non Soumis à TVA',
                    'rate' => 0.00,
                    'type' => 'zero',
                    'is_default' => false,
                    'collect_account_id' => null,
                    'deduct_account_id' => null,
                ],
                [
                    'code' => 'E',
                    'name' => 'Exonéré',
                    'rate' => 0.00,
                    'type' => 'exempt',
                    'is_default' => false,
                    'collect_account_id' => null,
                    'deduct_account_id' => null,
                ],
                [
                    'code' => 'SP',
                    'name' => 'TVA Spéciale (1%)',
                    'rate' => 1.00,
                    'type' => 'special',
                    'is_default' => false,
                    'collect_account_id' => $collectAccount?->id,
                    'deduct_account_id' => $deductAccount?->id,
                ],
            ];

            foreach ($rates as $rate) {
                VatRate::firstOrCreate(
                    ['client_id' => $client->id, 'code' => $rate['code']],
                    $rate
                );
            }
        }

        $count = VatRate::count();
        $this->command->info("✅ {$count} taux de TVA créés pour " . $clients->count() . " client(s).");
    }
}
