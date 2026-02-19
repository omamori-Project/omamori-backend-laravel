<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\FortuneColor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminFortuneColorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Admin 아니면 403 (목록)
     *
     * @return void
     */
    public function test_index_forbidden_when_not_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $res = $this->getJson('/api/v1/admin/fortune-colors');

        $res->assertStatus(403);
    }

    /**
     * Admin 목록 조회 성공
     *
     * @return void
     */
    public function test_index_success_when_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        FortuneColor::factory()->count(2)->create();

        Sanctum::actingAs($admin);

        $res = $this->getJson('/api/v1/admin/fortune-colors?withTrashed=1');

        $res->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * Admin 삭제는 soft delete + 204
     *
     * @return void
     */
    public function test_delete_soft_deletes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $color = FortuneColor::factory()->create();

        Sanctum::actingAs($admin);

        $res = $this->deleteJson("/api/v1/admin/fortune-colors/{$color->id}");

        $res->assertStatus(204);

        $this->assertSoftDeleted('fortune_colors', [
            'id' => $color->id,
        ]);
    }
}