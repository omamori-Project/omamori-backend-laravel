<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Frame;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Frame>
 */
class FrameFactory extends Factory
{
    protected $model = Frame::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' 프레임',
            'frame_key' => $this->faker->unique()->slug(2),
            'preview_url' => $this->faker->boolean(70) ? $this->faker->imageUrl() : null,
            'asset_file_id' => null,
            'meta' => [
                'type' => $this->faker->randomElement(['wood', 'metal', 'paper']),
            ],
            'is_active' => true,
        ];
    }

    /**
     * 비활성 상태
     *
     * @return static
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}