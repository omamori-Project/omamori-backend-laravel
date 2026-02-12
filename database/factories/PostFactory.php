<?php

namespace Database\Factories;

use App\Models\Omamori;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Post>
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'omamori_id' => null,

            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(3, true),

            'like_count' => 0,
            'comment_count' => 0,
            'bookmark_count' => 0,
            'view_count' => 0,
        ];
    }

    /**
     * 오마모리 연결 상태
     *
     * @param Omamori|null $omamori
     * @return static
     */
    public function withOmamori(?Omamori $omamori = null): static
    {
        return $this->state(function () use ($omamori): array {
            return [
                'omamori_id' => $omamori?->id ?? Omamori::factory(),
            ];
        });
    }

    /**
     * 특정 유저로 작성자 고정
     *
     * @param User $user
     * @return static
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
        ]);
    }
}