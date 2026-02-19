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
            'name'          => fake()->words(2, true),
            'frame_key'     => fake()->unique()->slug(2),
            // 스토리지 내부 경로를 저장 (public/s3 전환은 모델 accessor에서 url 생성)
            'preview_path'  => 'frames/' . fake()->unique()->slug(2) . '_preview.png',
            'is_default'    => false,
            'asset_file_id' => null,
            'meta' => [
                'type' => $this->faker->randomElement(['wood', 'metal', 'paper']),
            ],
            'is_active' => true,
        ];
    }

    /**
     * 기본 프레임 상태
     */
    public function default(): static
    {
        return $this->state(fn () => [
            'is_default' => true,
            'is_active' => true,
        ]);
    }
}