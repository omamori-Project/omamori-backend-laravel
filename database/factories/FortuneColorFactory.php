<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FortuneColorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'          => fake()->unique()->slug(2),
            'name'          => fake()->colorName(),
            'hex'           => fake()->hexColor(),
            'category'      => fake()->randomElement(['luck', 'love', 'health', 'wealth']),
            'short_meaning' => fake()->sentence(),
            'meaning'       => fake()->paragraph(),
            'tips'          => [fake()->sentence(), fake()->sentence()],
            'is_active'     => true,
        ];
    }
}