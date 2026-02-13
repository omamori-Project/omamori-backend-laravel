<?php

namespace Tests\Feature\Community;

use App\Models\Omamori;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
     * 피드 목록(공개) 조회 성공 - 숨김 게시글 제외
     *
     * GET /api/v1/posts
     *
     * @return void
     */
    public function test_index_feed_excludes_hidden_posts(): void
    {
        Post::factory()->count(2)->create();
        Post::factory()->hidden()->create();

        $response = $this->getJson('/api/v1/posts?page=1&size=10&sort=latest');

        $response->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertCount(2, $ids);
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
     * 게시글 상세(공개) 조회 실패 - 숨김 게시글
     *
     * GET /api/v1/posts/{post}
     *
     * @return void
     */
    public function test_show_404_when_hidden(): void
    {
        $post = Post::factory()->hidden()->create();

        $response = $this->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(404);
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

        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        $payload = [
            'title' => 'hello title',
            'content' => 'hello content',
            'omamori_id' => $omamori->id,
            'tags' => ['tag1', 'tag2'],
        ];

        $response = $this->postJson('/api/v1/posts', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'hello title')
            ->assertJsonPath('data.content', 'hello content')
            ->assertJsonPath('data.omamori_id', $omamori->id)
            ->assertJsonPath('data.tags.0', 'tag1')
            ->assertJsonPath('data.tags.1', 'tag2')
            ->assertJsonStructure([
                'data' => [
                    'omamori_snapshot',
                ],
            ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'hello title',
            'omamori_id' => $omamori->id,
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
            'omamori_id' => null,
        ];

        $response = $this->postJson('/api/v1/posts', $payload);

        $response->assertStatus(422);
    }

    /**
     * 게시글 작성 실패 - draft 오마모리 첨부 불가
     *
     * POST /api/v1/posts
     *
     * @return void
     */
    public function test_store_fails_when_omamori_not_published(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $payload = [
            'title' => 'hello title',
            'content' => 'hello content',
            'omamori_id' => $omamori->id,
        ];

        $response = $this->postJson('/api/v1/posts', $payload);

        $response->assertStatus(422);
    }

    /**
     * 게시글 작성 실패 - 타인 오마모리 첨부 불가
     *
     * POST /api/v1/posts
     *
     * @return void
     */
    public function test_store_fails_when_omamori_not_owned(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Sanctum::actingAs($other);

        $omamori = Omamori::factory()->for($owner)->create([
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        $payload = [
            'title' => 'hello title',
            'content' => 'hello content',
            'omamori_id' => $omamori->id,
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

        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        $post = Post::factory()->forUser($user)->withOmamori($omamori)->create();

        Sanctum::actingAs($user);

        $payload = [
            'title' => 'updated title',
            'content' => 'updated content',
            'omamori_id' => $omamori->id,
            'tags' => null,
        ];

        $response = $this->patchJson("/api/v1/posts/{$post->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'updated title')
            ->assertJsonPath('data.content', 'updated content');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'updated title',
        ]);
    }

    /**
     * 게시글 수정 성공 - omamori 변경 시 snapshot 재생성
     *
     * PATCH /api/v1/posts/{post}
     *
     * @return void
     */
    public function test_update_success_regenerates_snapshot_when_omamori_changes(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $omamoriA = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        $omamoriB = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        $post = Post::factory()->forUser($user)->withOmamori($omamoriA)->create([
            'omamori_snapshot' => ['id' => $omamoriA->id],
        ]);

        $payload = [
            'title' => 'updated title',
            'content' => 'updated content',
            'omamori_id' => $omamoriB->id,
            'tags' => ['x'],
        ];

        $response = $this->patchJson("/api/v1/posts/{$post->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.omamori_id', $omamoriB->id)
            ->assertJsonPath('data.tags.0', 'x')
            ->assertJsonPath('data.omamori_snapshot.id', $omamoriB->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'omamori_id' => $omamoriB->id,
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

        $omamori = Omamori::factory()->for($owner)->create([
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        $post = Post::factory()->forUser($owner)->withOmamori($omamori)->create();

        Sanctum::actingAs($other);

        $payload = [
            'title' => 'hacked title',
            'content' => 'hacked content',
            'omamori_id' => $omamori->id,
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
     * 내 게시글 목록 조회 성공 - 숨김 게시글 제외
     *
     * GET /api/v1/me/posts
     *
     * @return void
     */
    public function test_my_index_excludes_hidden_posts(): void
    {
        $user = User::factory()->create();

        Post::factory()->count(2)->forUser($user)->create();
        Post::factory()->forUser($user)->hidden()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me/posts?page=1&size=10&sort=latest');

        $response->assertStatus(200);

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

    /**
     * 특정 유저 게시글 목록 조회 성공 - 숨김 게시글 제외
     *
     * GET /api/v1/users/{userId}/posts
     *
     * @return void
     */
    public function test_user_index_excludes_hidden_posts(): void
    {
        $user = User::factory()->create();

        Post::factory()->count(3)->forUser($user)->create();
        Post::factory()->forUser($user)->hidden()->create();

        $viewer = User::factory()->create();
        Sanctum::actingAs($viewer);

        $response = $this->getJson("/api/v1/users/{$user->id}/posts?page=1&size=10&sort=latest");

        $response->assertStatus(200);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * 오마모리 삭제 시 연결된 게시글 숨김 처리
     *
     * DELETE /api/v1/omamoris/{id}
     *
     * @return void
     */
    public function test_posts_hidden_when_omamori_deleted(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        $postA = Post::factory()->forUser($user)->withOmamori($omamori)->create([
            'hidden_at' => null,
        ]);

        $postB = Post::factory()->forUser($user)->withOmamori($omamori)->create([
            'hidden_at' => null,
        ]);

        $response = $this->deleteJson("/api/v1/omamoris/{$omamori->id}");

        $response->assertStatus(204);

        $this->assertDatabaseHas('posts', [
            'id' => $postA->id,
        ]);
        $this->assertDatabaseHas('posts', [
            'id' => $postB->id,
        ]);

        $this->assertDatabaseMissing('posts', [
            'id' => $postA->id,
            'hidden_at' => null,
        ]);
        $this->assertDatabaseMissing('posts', [
            'id' => $postB->id,
            'hidden_at' => null,
        ]);

        $this->assertDatabaseHas('omamoris', [
            'id' => $omamori->id,
        ]);

        $response2 = $this->getJson("/api/v1/posts/{$postA->id}");
        $response2->assertStatus(404);
    }

    /**
     * 오마모리 published -> draft 전환 시 연결된 게시글 숨김 처리
     *
     * POST /api/v1/omamoris/{id}/save-draft
     *
     * @return void
     */
    public function test_posts_hidden_when_omamori_save_draft_from_published(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        $post = Post::factory()->forUser($user)->withOmamori($omamori)->create([
            'hidden_at' => null,
        ]);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/save-draft");

        $response->assertStatus(200);

        $this->assertDatabaseHas('omamoris', [
            'id' => $omamori->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
            'hidden_at' => null,
        ]);

        $response2 = $this->getJson("/api/v1/posts/{$post->id}");
        $response2->assertStatus(404);
    }

}