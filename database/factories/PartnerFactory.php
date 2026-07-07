<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['customer', 'supplier', 'both']),
            'code' => 'PRT-' . $this->faker->unique()->numerify('#####'),
            'company_name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'country' => 'Sénégal',
            'currency' => 'XOF',
            'status' => 'active',
        ];
    }

    public function customer(): static
    {
        return $this->state(fn() => ['type' => 'customer']);
    }

    public function supplier(): static
    {
        return $this->state(fn() => ['type' => 'supplier']);
    }
}
