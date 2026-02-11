<?php

namespace Tests\Feature\Share;

use App\Models\Omamori;
use App\Models\Share;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OmamoriShareTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_share_success_when_published(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->published()->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/omamoris/{$omamori->id}/share")
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token', fn ($v) => is_string($v) && strlen($v) > 0);
    }

    public function test_create_share_returns_existing_active_share_option_a(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->published()->create();

        $share = Share::factory()
            ->withOmamori($omamori)
            ->withUser($user)
            ->create(['is_active' => true, 'expires_at' => null]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/omamoris/{$omamori->id}/share")
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $share->id)
            ->assertJsonPath('data.token', $share->token);
    }

    public function test_create_share_fails_when_draft(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create(['status' => 'draft', 'published_at' => null]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/omamoris/{$omamori->id}/share")
            ->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_list_shares_success_only_owner(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->published()->create();

        Share::factory()->count(2)->withOmamori($omamori)->withUser($user)->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/omamoris/{$omamori->id}/shares")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_list_shares_forbidden_when_not_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $omamori = Omamori::factory()->for($owner)->published()->create();

        Sanctum::actingAs($other);

        $this->getJson("/api/v1/omamoris/{$omamori->id}/shares")
            ->assertStatus(403)
            ->assertJsonPath('success', false);
    }
}