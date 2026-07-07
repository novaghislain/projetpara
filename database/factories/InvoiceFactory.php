<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'FACT-' . $this->faker->unique()->numberBetween(1000, 9999),
            'type' => $this->faker->randomElement(['sale', 'purchase']),
            'partner_id' => function (array $attributes) {
                $partner = Partner::factory()->create([
                    'client_id' => $attributes['client_id'],
                ]);
                return $partner->id;
            },
            'partner_name' => function (array $attributes) {
                if (isset($attributes['partner_id'])) {
                    return Partner::find($attributes['partner_id'])?->company_name ?? '';
                }
                return $this->faker->company();
            },
            'invoice_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'due_date' => fn(array $attrs) => date('Y-m-d', strtotime($attrs['invoice_date'] . ' +30 days')),
            'subtotal' => $this->faker->randomFloat(0, 50000, 5000000),
            'vat_total' => fn(array $attrs) => round($attrs['subtotal'] * 0.18),
            'total' => fn(array $attrs) => $attrs['subtotal'] + $attrs['vat_total'],
            'paid_amount' => 0,
            'balance_due' => fn(array $attrs) => $attrs['total'],
            'status' => 'pending',
            'currency' => 'XOF',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn(array $attrs) => [
            'paid_amount' => $attrs['total'],
            'balance_due' => 0,
            'status' => 'paid',
        ]);
    }

    public function sale(): static
    {
        return $this->state(fn() => ['type' => 'sale']);
    }

    public function purchase(): static
    {
        return $this->state(fn() => ['type' => 'purchase']);
    }
}
