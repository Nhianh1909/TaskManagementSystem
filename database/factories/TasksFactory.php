<?php

namespace Database\Factories;
use App\Models\Sprints;
use App\Models\User;
use App\Models\TasksComments;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tasks>
 */
class TasksFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sprint_id'=>Sprints::inRandomOrder()->first()->id ?? Sprints::factory(),
            'created_by'=>User::inRandomOrder()->first()->id ?? User::factory(),
            'assigned_to'=>User::inRandomOrder()->first()->id ?? User::factory(),
            'title'=>fake()->sentence(),
            'description'=>fake()->paragraph(),
            'priority'=>fake()->randomElement(['low', 'medium', 'high']),
            'storyPoints'=>fake()->numberBetween(1, 13),
            'status' => fake()->randomElement(['toDo', 'inProgress', 'done']),
            'created_at' => now(),
            'updated_at' => now(),

        ];
    }
}
