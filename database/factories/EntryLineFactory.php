<?php

namespace Database\Factories;

use App\Models\EntryLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntryLineFactory extends Factory
{
    protected $model = EntryLine::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(0, 1000, 500000);

        return [
            'line_number' => $this->faker->numberBetween(1, 50),
            'account_code' => $this->faker->randomElement(['511', '411', '601', '701', '101']),
            'account_label' => $this->faker->word(),
            'description' => $this->faker->sentence(3),
            'debit' => fn() => $this->faker->boolean() ? $amount : 0,
            'credit' => fn(array $attrs) => $attrs['debit'] > 0 ? 0 : $amount,
            'currency' => 'XOF',
            'exchange_rate' => 1,
        ];
    }

    public function debit(float $amount): static
    {
        return $this->state(fn() => ['debit' => $amount, 'credit' => 0]);
    }

    public function credit(float $amount): static
    {
        return $this->state(fn() => ['debit' => 0, 'credit' => $amount]);
    }
}
