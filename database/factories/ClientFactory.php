<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'legal_form' => 'SARL',
            'rccm' => 'RB-' . fake()->unique()->numerify('######'),
            'ifu' => fake()->unique()->numerify('##########'),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'country' => 'Bénin',
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'status' => 'active',
            'contract_type' => 'saas',
            'contract_start' => now()->subMonths(6),
        ];
    }
}
