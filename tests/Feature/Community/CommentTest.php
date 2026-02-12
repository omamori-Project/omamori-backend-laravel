<?php

namespace Tests\Feature\Community;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 게시글 댓글 목록(공개) 조회 성공
     *
     * GET /api/v1/posts/{postId}/comments
     *
     * @return void
     */
    public function test_index_success_public(): void
    {
        $post = Post::factory()->create();

        $comment = Comment::factory()
            ->forPost($post)
            ->create();

        Comment::factory()
            ->forPost($post)
            ->asReply($comment)
            ->count(2)
            ->create();

        $response = $this->getJson("/api/v1/posts/{$post->id}/comments?page=1&size=20&sort=latest");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('children', $data[0]);
        $this->assertCount(2, $data[0]['children']);
    }

    /**
     * 댓글 작성 성공 + comment_count 증가(댓글+답글 합산)
     *
     * POST /api/v1/posts/{postId}/comments
     *
     * @return void
     */
    public function test_store_comment_success_and_increments_post_comment_count(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'comment_count' => 0,
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'content' => 'hello comment',
        ];

        $response = $this->postJson("/api/v1/posts/{$post->id}/comments", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.content', 'hello comment')
            ->assertJsonPath('data.post_id', $post->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'comment_count' => 1,
        ]);
    }

    /**
     * 답글 작성 성공 + comment_count 증가(댓글+답글 합산)
     *
     * POST /api/v1/comments/{commentId}/replies
     *
     * @return void
     */
    public function test_store_reply_success_and_increments_post_comment_count(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'comment_count' => 0,
        ]);

        $parent = Comment::factory()
            ->forPost($post)
            ->create();

        Sanctum::actingAs($user);

        $payload = [
            'content' => 'hello reply',
        ];

        $response = $this->postJson("/api/v1/comments/{$parent->id}/replies", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.content', 'hello reply')
            ->assertJsonPath('data.parent_id', $parent->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'comment_count' => 1,
        ]);
    }

    /**
     * 답글에 답글 작성 실패 (422)
     *
     * POST /api/v1/comments/{commentId}/replies
     *
     * @return void
     */
    public function test_store_reply_fails_when_parent_is_reply(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $parent = Comment::factory()->forPost($post)->create();
        $reply = Comment::factory()->forPost($post)->asReply($parent)->create();

        Sanctum::actingAs($user);

        $payload = [
            'content' => 'nested reply',
        ];

        $response = $this->postJson("/api/v1/comments/{$reply->id}/replies", $payload);

        $response->assertStatus(422);
    }

    /**
     * 댓글 수정 성공 - 작성자
     *
     * PATCH /api/v1/comments/{commentId}
     *
     * @return void
     */
    public function test_update_success_owner(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $comment = Comment::factory()
            ->forPost($post)
            ->forUser($user)
            ->create();

        Sanctum::actingAs($user);

        $payload = [
            'content' => 'updated content',
        ];

        $response = $this->patchJson("/api/v1/comments/{$comment->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.content', 'updated content');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'updated content',
        ]);
    }

    /**
     * 댓글 수정 실패 - 타인 forbidden
     *
     * @return void
     */
    public function test_update_forbidden_when_not_owner_or_admin(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create();

        $comment = Comment::factory()
            ->forPost($post)
            ->forUser($owner)
            ->create();

        Sanctum::actingAs($other);

        $payload = [
            'content' => 'hacked',
        ];

        $response = $this->patchJson("/api/v1/comments/{$comment->id}", $payload);

        $response->assertStatus(403);
    }

    /**
     * 댓글 수정 성공 - 관리자
     *
     * @return void
     */
    public function test_update_success_admin(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $post = Post::factory()->create();

        $comment = Comment::factory()
            ->forPost($post)
            ->forUser($owner)
            ->create();

        Sanctum::actingAs($admin);

        $payload = [
            'content' => 'admin updated',
        ];

        $response = $this->patchJson("/api/v1/comments/{$comment->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.content', 'admin updated');
    }

    /**
     * 댓글 삭제 성공 - 작성자 + comment_count 감소
     *
     * DELETE /api/v1/comments/{commentId}
     *
     * @return void
     */
    public function test_destroy_success_owner_and_decrements_post_comment_count(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'comment_count' => 1,
        ]);

        $comment = Comment::factory()
            ->forPost($post)
            ->forUser($user)
            ->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'comment_count' => 0,
        ]);
    }

    /**
     * 댓글 삭제 성공 - 관리자 + comment_count 감소
     *
     * @return void
     */
    public function test_destroy_success_admin_and_decrements_post_comment_count(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $post = Post::factory()->create([
            'comment_count' => 1,
        ]);

        $comment = Comment::factory()
            ->forPost($post)
            ->forUser($owner)
            ->create();

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'comment_count' => 0,
        ]);
    }

    /**
     * 댓글 삭제 실패 - 타인 forbidden (삭제 안 됨, count 변화 없음)
     *
     * @return void
     */
    public function test_destroy_forbidden_when_not_owner_or_admin(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $post = Post::factory()->create([
            'comment_count' => 1,
        ]);

        $comment = Comment::factory()
            ->forPost($post)
            ->forUser($owner)
            ->create();

        Sanctum::actingAs($other);

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'comment_count' => 1,
        ]);
    }

    /**
     * 내 댓글/답글 목록 조회 성공
     *
     * GET /api/v1/me/comments
     *
     * @return void
     */
    public function test_my_index_success(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Comment::factory()->forPost($post)->forUser($user)->count(2)->create();
        Comment::factory()->forPost($post)->count(3)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me/comments?page=1&size=20&sort=latest');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $this->assertCount(2, $response->json('data'));
    }
}