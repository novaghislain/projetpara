<?php

namespace Database\Factories;

use App\Models\AccountingAccount;
use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' - Compte courant',
            'bank_name' => $this->faker->randomElement(['Société Générale', 'Ecobank', 'BOA', 'Orange Money', 'Wave']),
            'account_number' => $this->faker->unique()->numerify('SN##########'),
            'iban' => 'SN' . $this->faker->numerify('##################'),
            'swift' => $this->faker->regexify('[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}'),
            'currency' => 'XOF',
            'type' => 'checking',
            'accounting_account_id' => function (array $attributes) {
                return AccountingAccount::factory()->create([
                    'client_id' => $attributes['client_id'],
                ])->id;
            },
            'opening_balance' => $this->faker->randomFloat(0, 100000, 10000000),
            'opening_date' => $this->faker->dateTimeBetween('-2 years', '-1 year')->format('Y-m-d'),
            'current_balance' => fn(array $attrs) => $attrs['opening_balance'],
            'reconciled_balance' => fn(array $attrs) => $attrs['opening_balance'],
            'is_active' => true,
            'is_default' => false,
        ];
    }
}
