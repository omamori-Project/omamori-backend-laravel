<?php

namespace Database\Factories;

use App\Models\Omamori;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
        $user = User::factory()->create();

        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        return [
            'user_id' => $user->id,
            'omamori_id' => $omamori->id,

            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(3, true),

            'omamori_snapshot' => [
                'id' => $omamori->id,
                'title' => $omamori->title ?? null,
                'status' => $omamori->status,
            ],
            'tags' => null,
            'hidden_at' => null,

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
            $target = $omamori ?? Omamori::factory()->create([
                'status' => 'published',
                'published_at' => Carbon::now(),
            ]);

            return [
                'omamori_id' => $target->id,
                'omamori_snapshot' => [
                    'id' => $target->id,
                    'title' => $target->title ?? null,
                    'status' => $target->status,
                ],
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
        return $this->state(function () use ($user): array {
            $omamori = Omamori::factory()->for($user)->create([
                'status' => 'published',
                'published_at' => Carbon::now(),
            ]);

            return [
                'user_id' => $user->id,
                'omamori_id' => $omamori->id,
                'omamori_snapshot' => [
                    'id' => $omamori->id,
                    'title' => $omamori->title ?? null,
                    'status' => $omamori->status,
                ],
            ];
        });
    }

    /**
     * 숨김 게시글 상태
     *
     * @return static
     */
    public function hidden(): static
    {
        return $this->state(fn (): array => [
            'hidden_at' => Carbon::now(),
        ]);
    }
}