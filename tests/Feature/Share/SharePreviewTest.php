<?php

namespace Tests\Feature\Public;

use App\Models\Omamori;
use App\Models\Share;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * GET /api/v1/public/shares/{token}/preview
 */
class SharePreviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 유효 토큰 미리보기 성공 + view_count 증가
     *
     * @return void
     */
    public function test_preview_success_and_increments_view_count(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $share = Share::factory()->create([
            'user_id' => $user->id,
            'omamori_id' => $omamori->id,
            'token' => (string) Str::uuid(),
            'is_active' => true,
            'expires_at' => null,
            'view_count' => 0,
        ]);

        $res = $this->getJson("/api/v1/public/shares/{$share->token}/preview");

        $res->assertStatus(200)
            ->assertJsonPath('data.token', $share->token)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'view_count',
                    'expires_at',
                    'created_at',
                    'omamori' => [
                        'id',
                        'status',
                    ],
                ],
            ]);

        $share->refresh();
        $this->assertSame(1, (int) $share->view_count);
    }

    /**
     * 비활성 토큰은 404
     *
     * @return void
     */
    public function test_preview_404_when_inactive(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $share = Share::factory()->create([
            'user_id' => $user->id,
            'omamori_id' => $omamori->id,
            'token' => (string) Str::uuid(),
            'is_active' => false,
            'expires_at' => null,
        ]);

        $this->getJson("/api/v1/public/shares/{$share->token}/preview")
            ->assertStatus(404);
    }

    /**
     * 만료 토큰은 404
     *
     * @return void
     */
    public function test_preview_404_when_expired(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $share = Share::factory()->create([
            'user_id' => $user->id,
            'omamori_id' => $omamori->id,
            'token' => (string) Str::uuid(),
            'is_active' => true,
            'expires_at' => now()->subMinute(),
        ]);

        $this->getJson("/api/v1/public/shares/{$share->token}/preview")
            ->assertStatus(404);
    }

    /**
     * 연결된 오마모리가 published가 아니면 404
     *
     * @return void
     */
    public function test_preview_404_when_omamori_not_published(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $share = Share::factory()->create([
            'user_id' => $user->id,
            'omamori_id' => $omamori->id,
            'token' => (string) Str::uuid(),
            'is_active' => true,
            'expires_at' => null,
        ]);

        $this->getJson("/api/v1/public/shares/{$share->token}/preview")
            ->assertStatus(404);
    }
}