<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Frame;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminFrameTest extends TestCase
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

        $res = $this->getJson('/api/v1/admin/frames');

        $res->assertStatus(403);
    }

    /**
     * Admin 삭제는 soft delete + 204
     *
     * @return void
     */
    public function test_delete_soft_deletes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $frame = Frame::factory()->create();

        Sanctum::actingAs($admin);

        $res = $this->deleteJson("/api/v1/admin/frames/{$frame->id}");

        $res->assertStatus(204);

        $this->assertSoftDeleted('frames', [
            'id' => $frame->id,
        ]);
    }
}