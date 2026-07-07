<?php

namespace Database\Factories;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-6 months', 'now');
        $dateStr = $date->format('Y-m-d');
        $randomNum = $this->faker->unique()->numberBetween(1, 9999);

        return [
            'entry_date' => $dateStr,
            'entry_number' => 'EC-' . str_replace('-', '', $dateStr) . '-' . $randomNum,
            'reference' => 'EC-' . $dateStr . '-' . $randomNum,
            'description' => $this->faker->sentence(4),
            'status' => 'posted',
            'total_debit' => 0,
            'total_credit' => 0,
            'is_balanced' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn() => ['status' => 'draft']);
    }

    public function forDate(string $date): static
    {
        return $this->state(fn() => ['entry_date' => $date]);
    }
}
