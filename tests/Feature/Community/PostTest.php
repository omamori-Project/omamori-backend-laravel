<?php

namespace Tests\Feature\Community;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 피드 목록(공개) 조회 성공
     *
     * GET /api/v1/posts
     *
     * @return void
     */
    public function test_index_feed_success_public(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/posts?page=1&size=10&sort=latest');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    /**
     * 게시글 상세(공개) 조회 성공 + 조회수 증가
     *
     * GET /api/v1/posts/{post}
     *
     * @return void
     */
    public function test_show_success_public_and_increments_view_count(): void
    {
        $post = Post::factory()->create([
            'view_count' => 0,
        ]);

        $response = $this->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $post->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'view_count' => 1,
        ]);
    }

    /**
     * 게시글 작성 성공
     *
     * POST /api/v1/posts
     *
     * @return void
     */
    public function test_store_success(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'hello title',
            'content' => 'hello content',
            'omamori_id' => null,
        ];

        $response = $this->postJson('/api/v1/posts', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'hello title')
            ->assertJsonPath('data.content', 'hello content');

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'hello title',
        ]);
    }

    /**
     * 게시글 작성 실패 - validation
     *
     * POST /api/v1/posts
     *
     * @return void
     */
    public function test_store_fails_validation(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => '',
            'content' => '',
        ];

        $response = $this->postJson('/api/v1/posts', $payload);

        $response->assertStatus(422);
    }

    /**
     * 게시글 수정 성공 - 작성자
     *
     * PATCH /api/v1/posts/{post}
     *
     * @return void
     */
    public function test_update_success_owner(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->forUser($user)->create();

        Sanctum::actingAs($user);

        $payload = [
            'title' => 'updated title',
        ];

        $response = $this->patchJson("/api/v1/posts/{$post->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'updated title');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'updated title',
        ]);
    }

    /**
     * 게시글 수정 실패 - 타인 forbidden
     *
     * PATCH /api/v1/posts/{post}
     *
     * @return void
     */
    public function test_update_forbidden_when_not_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->forUser($owner)->create();

        Sanctum::actingAs($other);

        $payload = [
            'title' => 'hacked title',
        ];

        $response = $this->patchJson("/api/v1/posts/{$post->id}", $payload);

        $response->assertStatus(403);
    }

    /**
     * 게시글 삭제 성공 - 작성자
     *
     * DELETE /api/v1/posts/{post}
     *
     * @return void
     */
    public function test_destroy_success_owner(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->forUser($user)->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * 게시글 삭제 성공 - 관리자
     *
     * DELETE /api/v1/posts/{post}
     *
     * @return void
     */
    public function test_destroy_success_admin(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $post = Post::factory()->forUser($owner)->create();

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * 게시글 삭제 실패 - 타인 forbidden
     *
     * DELETE /api/v1/posts/{post}
     *
     * @return void
     */
    public function test_destroy_forbidden_when_not_owner_or_admin(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $post = Post::factory()->forUser($owner)->create();

        Sanctum::actingAs($other);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * 내 게시글 목록 조회 성공
     *
     * GET /api/v1/me/posts
     *
     * @return void
     */
    public function test_my_index_success(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->forUser($user)->create();
        Post::factory()->count(3)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me/posts?page=1&size=10&sort=latest');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    /**
     * 특정 유저 게시글 목록 조회 성공
     *
     * GET /api/v1/users/{userId}/posts
     *
     * @return void
     */
    public function test_user_index_success(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->forUser($user)->create();

        $viewer = User::factory()->create();
        Sanctum::actingAs($viewer);

        $response = $this->getJson("/api/v1/users/{$user->id}/posts?page=1&size=10&sort=latest");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $this->assertCount(3, $response->json('data'));
    }
}