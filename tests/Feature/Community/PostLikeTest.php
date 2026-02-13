<?php

namespace Tests\Feature\Community;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostLikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 좋아요 추가 성공 (POST /api/v1/posts/{post}/likes)
     *
     * @return void
     */
    public function test_like_success(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/posts/{$post->id}/likes");

        $response->assertStatus(204);

        $this->assertDatabaseHas('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * 좋아요 추가는 멱등적이다 (이미 좋아요가 있으면 그대로 성공)
     *
     * @return void
     */
    public function test_like_is_idempotent_when_already_liked(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        PostLike::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/posts/{$post->id}/likes");

        $response->assertStatus(204);

        $this->assertDatabaseCount('post_likes', 1);
    }

    /**
     * 좋아요 취소 성공 (DELETE /api/v1/posts/{post}/likes)
     *
     * @return void
     */
    public function test_unlike_success(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        PostLike::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}/likes");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * 좋아요 취소는 멱등적이다 (좋아요가 없어도 성공)
     *
     * @return void
     */
    public function test_unlike_is_idempotent_when_not_liked(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}/likes");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * 내 좋아요 여부 조회 성공 (GET /api/v1/posts/{post}/likes/me)
     *
     * @return void
     */
    public function test_like_me_status_success(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/posts/{$post->id}/likes/me");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.liked', false);

        PostLike::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $response2 = $this->getJson("/api/v1/posts/{$post->id}/likes/me");

        $response2->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.liked', true);
    }

    /**
     * 비로그인 시 좋아요 API는 401
     *
     * @return void
     */
    public function test_like_requires_auth(): void
    {
        $post = Post::factory()->create();

        $this->postJson("/api/v1/posts/{$post->id}/likes")
            ->assertStatus(401);

        $this->deleteJson("/api/v1/posts/{$post->id}/likes")
            ->assertStatus(401);

        $this->getJson("/api/v1/posts/{$post->id}/likes/me")
            ->assertStatus(401);
    }

    /**
     * 존재하지 않는 게시글이면 404
     *
     * @return void
     */
    public function test_like_404_when_post_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $notFoundId = 999999;

        $this->postJson("/api/v1/posts/{$notFoundId}/likes")
            ->assertStatus(404);

        $this->deleteJson("/api/v1/posts/{$notFoundId}/likes")
            ->assertStatus(404);

        $this->getJson("/api/v1/posts/{$notFoundId}/likes/me")
            ->assertStatus(404);
    }
}