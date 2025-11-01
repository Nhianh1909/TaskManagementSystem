<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Retrospective;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RetrospectiveItem>
 */
class RetrospectiveItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'retrospective_id' => Retrospective::factory(), // Tạo 1 buổi họp mới
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(), // Lấy 1 user ngẫu nhiên
            'content' => fake()->sentence(7),
            'type' => fake()->randomElement(['bad', 'good', 'action']),
        ];
    }
}
