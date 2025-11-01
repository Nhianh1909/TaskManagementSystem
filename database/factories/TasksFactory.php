<?php

namespace Database\Factories;
use App\Models\Sprints;
use App\Models\User;
use App\Models\TasksComments;
use App\Models\Epics;
use App\Models\Tasks;
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
            // Mặc định factory tạo ra 1 User Story (task cha)
            // nên 'parent_id' là null.
            'parent_id' => null,

            // Một User Story (cha) thì nên thuộc về 1 Epic.
            // Factory sẽ cố lấy 1 Epic ngẫu nhiên, nếu không có, nó tự tạo 1 Epic mới.
            'epic_id' => Epics::inRandomOrder()->first()?->id ?? Epics::factory(),

        ];
    }
    /**
     * Định nghĩa trạng thái cho một Sub-task
     * (có 'parent_id' và không cần 'epic_id' hoặc 'storyPoints')
     */
    public function subtask(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                // Tạo một User Story (cha) mới cho Sub-task này
                'parent_id' => Tasks::factory(),

                // Sub-task không cần liên kết trực tiếp với Epic
                'epic_id' => null,

                // Sub-task thường không có story point (User Story cha mới có)
                'storyPoints' => 0,
            ];
        });
    }
}

