<?php

namespace Tests\Feature\Community;

use App\Models\Post;
use App\Models\PostBookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostBookmarkTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 북마크 추가 성공 (POST /api/v1/posts/{post}/bookmarks)
     *
     * @return void
     */
    public function test_bookmark_success(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/posts/{$post->id}/bookmarks");

        $response->assertStatus(204);

        $this->assertDatabaseHas('post_bookmarks', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * 북마크 추가는 멱등적이다 (이미 북마크면 그대로 성공)
     *
     * @return void
     */
    public function test_bookmark_is_idempotent_when_already_bookmarked(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        PostBookmark::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/posts/{$post->id}/bookmarks");

        $response->assertStatus(204);

        $this->assertDatabaseCount('post_bookmarks', 1);
    }

    /**
     * 북마크 취소 성공 (DELETE /api/v1/posts/{post}/bookmarks)
     *
     * @return void
     */
    public function test_unbookmark_success(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        PostBookmark::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}/bookmarks");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('post_bookmarks', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * 북마크 취소는 멱등적이다 (북마크가 없어도 성공)
     *
     * @return void
     */
    public function test_unbookmark_is_idempotent_when_not_bookmarked(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}/bookmarks");

        $response->assertStatus(204);
    }

    /**
     * 내 북마크 목록 조회 성공 (GET /api/v1/me/bookmarks)
     *
     * @return void
     */
    public function test_my_bookmarks_index_success(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
    
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $otherPost = Post::factory()->create();
    
        PostBookmark::factory()->create([
            'post_id' => $post1->id,
            'user_id' => $user->id,
        ]);
        PostBookmark::factory()->create([
            'post_id' => $post2->id,
            'user_id' => $user->id,
        ]);
        PostBookmark::factory()->create([
            'post_id' => $otherPost->id,
            'user_id' => $other->id,
        ]);
    
        Sanctum::actingAs($user);
    
        $response = $this->getJson('/api/v1/me/bookmarks?size=10');
    
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    
        $ids = collect($response->json('data'))
            ->map(fn ($row) => $row['post']['id'] ?? null)
            ->filter()
            ->values();
    
        $this->assertTrue($ids->contains($post1->id));
        $this->assertTrue($ids->contains($post2->id));
        $this->assertFalse($ids->contains($otherPost->id));
    }
    
    /**
     * 내 북마크 목록 조회 실패 (size 유효성)
     *
     * @return void
     */
    public function test_my_bookmarks_index_fails_when_size_invalid(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me/bookmarks?size=0')
            ->assertStatus(422);

        $this->getJson('/api/v1/me/bookmarks?size=101')
            ->assertStatus(422);

        $this->getJson('/api/v1/me/bookmarks?page=0')
            ->assertStatus(422);
    }

    /**
     * 비로그인 시 북마크 API는 401
     *
     * @return void
     */
    public function test_bookmark_requires_auth(): void
    {
        $post = Post::factory()->create();

        $this->postJson("/api/v1/posts/{$post->id}/bookmarks")
            ->assertStatus(401);

        $this->deleteJson("/api/v1/posts/{$post->id}/bookmarks")
            ->assertStatus(401);

        $this->getJson('/api/v1/me/bookmarks')
            ->assertStatus(401);
    }

    /**
     * 존재하지 않는 게시글이면 404
     *
     * @return void
     */
    public function test_bookmark_404_when_post_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $notFoundId = 999999;

        $this->postJson("/api/v1/posts/{$notFoundId}/bookmarks")
            ->assertStatus(404);

        $this->deleteJson("/api/v1/posts/{$notFoundId}/bookmarks")
            ->assertStatus(404);
    }
}