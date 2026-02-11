<?php

namespace Tests\Feature\Share;

use App\Models\Omamori;
use App\Models\Share;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShareManageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_update_share_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->published()->create();

        $share = Share::factory()->withOmamori($omamori)->withUser($user)->create([
            'is_active' => true,
            'expires_at' => null,
        ]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/shares/{$share->id}", [
            'is_active' => false,
            'expires_at' => now()->addDay()->toISOString(),
        ])
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'is_active' => false,
        ]);
    }

    public function test_update_share_rotate_token(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->published()->create();

        $share = Share::factory()->withOmamori($omamori)->withUser($user)->create();

        $oldToken = $share->token;

        Sanctum::actingAs($user);

        $res = $this->patchJson("/api/v1/shares/{$share->id}", [
            'rotate_token' => true,
        ]);

        $res->assertStatus(200)->assertJsonPath('success', true);

        $newToken = $res->json('data.token');
        $this->assertIsString($newToken);
        $this->assertNotSame($oldToken, $newToken);
    }

    public function test_update_share_forbidden_when_not_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $omamori = Omamori::factory()->for($owner)->published()->create();

        $share = Share::factory()->withOmamori($omamori)->withUser($owner)->create();

        Sanctum::actingAs($other);

        $this->patchJson("/api/v1/shares/{$share->id}", ['is_active' => false])
            ->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_delete_share_no_content(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->published()->create();

        $share = Share::factory()->withOmamori($omamori)->withUser($user)->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/shares/{$share->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('shares', ['id' => $share->id]);
    }
}