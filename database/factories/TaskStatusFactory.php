<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TaskStatus;

class TaskStatusFactory extends Factory
{
    protected $model = TaskStatus::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word;
        return [
            'name' => ucfirst($name),
            'order_index' => $this->faker->numberBetween(1, 100),
            'is_done' => false,
            'color_class' => 'border-gray-300',
            'team_id' => null, // Mặc định là global
        ];
    }

    // State: Trạng thái "Done"
    public function done()
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Done',
            'is_done' => true,
            'color_class' => 'border-green-500',
        ]);
    }
}
