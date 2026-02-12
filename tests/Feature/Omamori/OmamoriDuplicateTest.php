<?php

namespace Tests\Feature\Omamori;

use App\Models\Omamori;
use App\Models\OmamoriElement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * POST /api/v1/omamoris/{omamoriId}/duplicate
 */
class OmamoriDuplicateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 본인 오마모리 복제 성공 + 요소도 복제됨
     *
     * @return void
     */
    public function test_duplicate_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 원본 요소 3개 생성 (background 포함해도 됨)
        OmamoriElement::factory()->for($omamori)->create([
            'type' => 'background',
            'layer' => 0,
        ]);
        OmamoriElement::factory()->for($omamori)->create([
            'type' => 'text',
            'layer' => 1,
        ]);
        OmamoriElement::factory()->for($omamori)->create([
            'type' => 'stamp',
            'layer' => 2,
        ]);

        Sanctum::actingAs($user);

        $res = $this->postJson("/api/v1/omamoris/{$omamori->id}/duplicate");

        // created()를 쓰면 보통 201
        $res->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                ],
            ]);

        $newId = (int) $res->json('data.id');

        $this->assertNotSame($omamori->id, $newId);

        // 복제본 상태 정책 확인: draft + published_at null
        $this->assertDatabaseHas('omamoris', [
            'id' => $newId,
            'user_id' => $user->id,
            'status' => 'draft',
            'published_at' => null,
        ]);

        // 요소 복제 개수 확인
        $this->assertSame(
            3,
            OmamoriElement::query()->where('omamori_id', $newId)->count()
        );
    }

    /**
     * 타인 오마모리 복제는 403
     *
     * @return void
     */
    public function test_duplicate_forbidden_when_other_users_omamori(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $omamori = Omamori::factory()->for($owner)->create();

        Sanctum::actingAs($other);

        $res = $this->postJson("/api/v1/omamoris/{$omamori->id}/duplicate");

        $res->assertStatus(403);
    }

    /**
     * 인증 없이 복제는 401
     *
     * @return void
     */
    public function test_duplicate_unauthorized(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create();

        $res = $this->postJson("/api/v1/omamoris/{$omamori->id}/duplicate");

        $res->assertStatus(401);
    }
}