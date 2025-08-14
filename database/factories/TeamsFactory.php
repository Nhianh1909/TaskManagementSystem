<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teams>
 */
class TeamsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=>fake()->company(),//sinh ra một tên ngẫu nhiên
            'description'=>fake()->sentence(),//sinh ra một câu ngẫu nhiên
            'created_at'=>fake()->date(),//sinh ra một ngày ngẫu nhiên
            'updated_at'=>fake()->date()
        ];
    }
}
