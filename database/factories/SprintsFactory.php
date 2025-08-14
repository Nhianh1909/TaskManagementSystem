<?php

namespace Database\Factories;
use App\Models\Teams;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sprints>
 */
class SprintsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Teams::inRandomOrder()->first()->id ?? Teams::factory(),
            'name' => fake()->word(),
            'goal' => fake()->sentence(),
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => fake()->randomElement(['planning', 'inProgress', 'completed']),
            'is_active' => fake()->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
