<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FortuneColor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FortuneColor>
 */
class FortuneColorFactory extends Factory
{
    protected $model = FortuneColor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hex = strtoupper($this->faker->hexColor());

        return [
            'code' => $this->faker->unique()->slug(2),
            'name' => $this->faker->word(),
            'hex' => $hex,
            'category' => $this->faker->randomElement(['love', 'study', 'money', null]),
            'short_meaning' => $this->faker->sentence(6),
            'meaning' => $this->faker->paragraph(),
            'tips' => [$this->faker->sentence(5), $this->faker->sentence(5)],
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