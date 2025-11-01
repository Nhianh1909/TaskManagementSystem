<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sprints;
use App\Models\Teams;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Retrospective>
 */
class RetrospectiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sprint_id' => Sprints::factory(), // Tạo 1 sprint mới
            'team_id' => Teams::factory(),     // Tạo 1 team mới
        ];
    }
}
