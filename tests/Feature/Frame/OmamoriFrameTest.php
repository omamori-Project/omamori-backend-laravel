<?php

declare(strict_types=1);

namespace Tests\Feature\Frame;

use App\Models\Frame;
use App\Models\Omamori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OmamoriFrameTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_frame_success(): void
    {
        $user = User::factory()->create();

        $baseFrame = Frame::factory()->create(['is_active' => true]);

        $omamori = Omamori::factory()->for($user)->create([
            'applied_frame_id' => $baseFrame->id,
        ]);

        $newFrame = Frame::factory()->create(['is_active' => true]);

        Sanctum::actingAs($user);

        $res = $this->postJson("/api/v1/omamoris/{$omamori->id}/frame", [
            'frameId' => $newFrame->id,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.applied_frame_id', $newFrame->id);

        $this->assertDatabaseHas('omamoris', [
            'id' => $omamori->id,
            'applied_frame_id' => $newFrame->id,
        ]);
    }

    public function test_apply_frame_fails_when_inactive(): void
    {
        $user = User::factory()->create();

        $baseFrame = Frame::factory()->create(['is_active' => true]);
        $omamori = Omamori::factory()->for($user)->create([
            'applied_frame_id' => $baseFrame->id,
        ]);

        $inactive = Frame::factory()->inactive()->create();

        Sanctum::actingAs($user);

        $res = $this->postJson("/api/v1/omamoris/{$omamori->id}/frame", [
            'frameId' => $inactive->id,
        ]);

        $res->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}