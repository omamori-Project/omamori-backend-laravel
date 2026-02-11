<?php

namespace Database\Factories;

use App\Models\Omamori;
use App\Models\OmamoriElement;
use Illuminate\Database\Eloquent\Factories\Factory;

class OmamoriElementFactory extends Factory
{
    protected $model = OmamoriElement::class;

    /**
     * 기본 요소 정의
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'omamori_id' => Omamori::factory(),
            'type' => 'stamp',
            'layer' => 1,
            'props' => [
                'asset_key' => fake()->randomElement(['heart', 'star', 'flower']),
            ],
            'transform' => [
                'x' => fake()->numberBetween(0, 300),
                'y' => fake()->numberBetween(0, 500),
                'scale' => 1,
                'rotate' => 0,
            ],
        ];
    }

    /**
     * 특정 오마모리에 소속
     *
     * @param Omamori $omamori
     * @return static
     */
    public function forOmamori(Omamori $omamori): static
    {
        return $this->state(fn () => [
            'omamori_id' => $omamori->id,
        ]);
    }

    /**
     * background 요소
     *
     * @return static
     */
    public function background(): static
    {
        return $this->state(fn () => [
            'type' => 'background',
            'layer' => 0,
            'props' => [
                'kind' => 'solid',
                'color' => fake()->hexColor(),
            ],
            'transform' => [],
        ]);
    }

    /**
     * stamp 요소
     *
     * @param string|null $assetKey
     * @return static
     */
    public function stamp(?string $assetKey = null): static
    {
        return $this->state(fn () => [
            'type' => 'stamp',
            'props' => [
                'asset_key' => $assetKey ?? fake()->randomElement(['heart', 'star', 'flower']),
            ],
        ]);
    }

    /**
     * text 요소
     *
     * @param string|null $text
     * @return static
     */
    public function text(?string $text = null): static
    {
        return $this->state(fn () => [
            'type' => 'text',
            'props' => [
                'text' => $text ?? fake()->sentence(),
            ],
        ]);
    }
}