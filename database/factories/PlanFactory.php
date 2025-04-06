<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->monthName(),
            'description' => fake()->text(20),
            'price' => fake()->numberBetween(10, 100),
            'duration' => '2',
            'duration_unit' => 'day',
        ];
    }
    public function trial(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'New User Free Trial',
                'description' => 'Free Trial Plan (Do not Delete)',
                'price' => '0.00',
                'duration' => '3',
                'duration_unit' => 'day',
            ];
        });
    }
}
