<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FrameFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => fake()->words(2, true),
            'frame_key'     => fake()->unique()->slug(2),
            'preview_url'   => fake()->imageUrl(),
            'asset_file_id' => null,
            'is_active'     => true,
            'meta'          => [],
        ];
    }
}