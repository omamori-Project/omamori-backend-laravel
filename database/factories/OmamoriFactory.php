<?php

namespace Database\Factories;

use App\Models\FortuneColor;
use App\Models\Frame;
use App\Models\Omamori;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OmamoriFactory extends Factory
{
    protected $model = Omamori::class;
    /**
     * 기본 상태 정의
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'                  => User::factory(),
            'title'                    => fake()->sentence(3),
            'meaning'                  => fake()->sentence(),
            'status'                   => 'draft',
            'theme'                    => fake()->randomElement(['love', 'health', 'academic', 'business']),
            'size_code'                => fake()->randomElement(['small', 'medium', 'large']),
            'back_message'             => null,
            'applied_fortune_color_id' => null,
            'applied_frame_id'         => Frame::factory(),
            'preview_file_id'          => null,
            'view_count'               => 0,
            'published_at'             => null,
        ];
    }
    /**
     * published 상태
     *
     * @return static
     */
    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => 'published',
            'published_at' => now(),
        ]);
    }
    /**
     * 행운 색상 적용
     *
     * @return static
     */
    public function withFortuneColor(): static
    {
        return $this->state(fn () => [
            'applied_fortune_color_id' => FortuneColor::factory(),
        ]);
    }

    /**
     * 프레임 적용
     *
     * @return static
     */    public function withFrame(): static
    {
        return $this->state(fn () => [
            'applied_frame_id' => Frame::factory(),
        ]);
    }

    /**
     * 기본 프레임 적용 상태
     */
    public function withDefaultFrame(): static
    {
        return $this->state(fn () => [
            'applied_frame_id' => Frame::factory()->default(),
        ]);
    }
}