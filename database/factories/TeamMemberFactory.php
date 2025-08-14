<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Teams;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id'=>Teams::inRandomOrder()->first()->id ?? Teams::factory(),
            'user_id'=>User::inRandomOrder()->first()->id ?? User::factory(),
            'roleInTeam'=>fake()->randomElement(['admin', 'product_owner','scrum_master', 'leadDeveloper', 'developer']),
            'joined_at'=>fake()->dateTimeBetween('-1 year', 'now'),
            'created_at'=>now(),
            'updated_at'=>now(),
        ];
    }
}
