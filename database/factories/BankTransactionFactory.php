<?php

namespace Database\Factories;

use App\Models\BankTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankTransactionFactory extends Factory
{
    protected $model = BankTransaction::class;

    public function definition(): array
    {
        $isDebit = $this->faker->boolean();
        $amount = $this->faker->randomFloat(0, 10000, 2000000);

        return [
            'transaction_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'value_date' => fn(array $attrs) => $attrs['transaction_date'],
            'description' => $this->faker->sentence(4),
            'debit' => $isDebit ? $amount : 0,
            'credit' => $isDebit ? 0 : $amount,
            'balance' => $this->faker->randomFloat(0, 100000, 5000000),
            'reference' => 'TXN-' . $this->faker->unique()->numerify('#######'),
            'category' => $this->faker->randomElement(['ventes', 'achats', 'salaires', 'frais', 'impôts']),
            'status' => 'cleared',
            'is_reconciled' => false,
            'is_imported' => false,
        ];
    }

    public function reconciled(): static
    {
        return $this->state(fn() => [
            'is_reconciled' => true,
            'status' => 'reconciled',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn() => [
            'status' => 'pending',
        ]);
    }
}
