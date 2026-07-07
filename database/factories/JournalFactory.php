<?php

namespace Database\Factories;

use App\Models\Journal;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalFactory extends Factory
{
    protected $model = Journal::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->randomElement(['OD', 'VT', 'AC', 'BQ', 'CA', 'AN']),
            'label' => $this->faker->randomElement([
                'Opérations Diverses', 'Ventes', 'Achats', 'Banque', 'Caisse', 'A Nouveaux',
            ]),
            'type' => 'general',
            'is_active' => true,
            'is_default' => false,
            'next_number' => 1,
        ];
    }

    public function ofType(string $code): static
    {
        $labels = [
            'OD' => 'Opérations Diverses',
            'VT' => 'Ventes',
            'AC' => 'Achats',
            'BQ' => 'Banque',
            'CA' => 'Caisse',
            'AN' => 'A Nouveaux',
        ];

        return $this->state(fn() => [
            'code' => $code,
            'label' => $labels[$code] ?? $code,
        ]);
    }
}
