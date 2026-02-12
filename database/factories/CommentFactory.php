<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * @var class-string<Comment>
     */
    protected $model = Comment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'content' => $this->faker->sentence(12),
        ];
    }

    /**
     * 특정 게시글에 소속
     *
     * @param Post $post
     * @return static
     */
    public function forPost(Post $post): static
    {
        return $this->state(fn (): array => [
            'post_id' => $post->id,
        ]);
    }

    /**
     * 특정 유저가 작성
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

    /**
     * 답글 상태로 생성 (parent 지정)
     *
     * @param Comment $parent
     * @return static
     */
    public function asReply(Comment $parent): static
    {
        return $this->state(fn (): array => [
            'post_id' => $parent->post_id,
            'parent_id' => $parent->id,
        ]);
    }
}