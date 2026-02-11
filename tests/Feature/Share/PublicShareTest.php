<?php

namespace Tests\Feature\Share;

use App\Models\Omamori;
use App\Models\Share;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicShareTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_show_success(): void
    {
        $omamori = Omamori::factory()->published()->create();

        $share = Share::factory()
            ->withOmamori($omamori)
            ->withUser($omamori->user)
            ->create(['is_active' => true, 'expires_at' => null]);

        $this->getJson("/api/v1/public/shares/{$share->token}")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.share.token', $share->token)
            ->assertJsonPath('data.omamori.id', $omamori->id);
    }

    public function test_public_show_404_when_inactive(): void
    {
        $omamori = Omamori::factory()->published()->create();

        $share = Share::factory()
            ->withOmamori($omamori)
            ->withUser($omamori->user)
            ->inactive()
            ->create();

        $this->getJson("/api/v1/public/shares/{$share->token}")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_public_show_404_when_expired(): void
    {
        $omamori = Omamori::factory()->published()->create();

        $share = Share::factory()
            ->withOmamori($omamori)
            ->withUser($omamori->user)
            ->expired()
            ->create();

        $this->getJson("/api/v1/public/shares/{$share->token}")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_public_show_404_when_omamori_not_published(): void
    {
        $omamori = Omamori::factory()->create(['status' => 'draft', 'published_at' => null]);

        $share = Share::factory()
            ->withOmamori($omamori)
            ->withUser($omamori->user)
            ->create(['is_active' => true]);

        $this->getJson("/api/v1/public/shares/{$share->token}")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_public_show_increments_view_count(): void
    {
        $omamori = Omamori::factory()->published()->create();

        $share = Share::factory()
            ->withOmamori($omamori)
            ->withUser($omamori->user)
            ->create(['view_count' => 0]);

        $this->getJson("/api/v1/public/shares/{$share->token}")
            ->assertStatus(200);

        $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'view_count' => 1,
        ]);
    }
}