<?php

namespace Database\Factories;

use App\Models\BankReconciliationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankReconciliationItemFactory extends Factory
{
    protected $model = BankReconciliationItem::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['debit', 'credit']),
            'status' => 'cleared',
            'amount' => $this->faker->randomFloat(0, 10000, 1000000),
        ];
    }
}
