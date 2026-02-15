<?php

declare(strict_types=1);

namespace Tests\Feature\Frame;

use App\Models\Frame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FramePublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_active_frames(): void
    {
        Frame::factory()->count(2)->create(['is_active' => true]);
        Frame::factory()->count(2)->inactive()->create();

        $res = $this->getJson('/api/v1/frames');

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.is_active', true);
    }
}