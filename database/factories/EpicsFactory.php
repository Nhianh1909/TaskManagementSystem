<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Teams;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Epics>
 */
class EpicsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //lấy team đầu tiên trong bảng teams để liên kết với epic
            'team_id' => Teams::first()->id,
            'title' =>'Epic: ' . fake()->sentence(3),
            'description' => fake()->paragraph(3),
        ];

    }
}
