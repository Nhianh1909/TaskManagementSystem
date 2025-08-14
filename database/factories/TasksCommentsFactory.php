<?php

namespace Database\Factories;
use App\Models\Tasks;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TasksComments>
 */
class TasksCommentsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Tasks::inRandomOrder()->first()->id ?? Tasks::factory(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'parent_id' => null, // hoặc random nếu muốn test trả lời comment
            'content' => fake()->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
