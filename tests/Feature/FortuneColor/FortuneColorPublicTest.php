<?php

declare(strict_types=1);

namespace Tests\Feature\FortuneColor;

use App\Models\FortuneColor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FortuneColorPublicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * GET /api/v1/fortune-colors (활성만 기본)
     *
     * @return void
     */
    public function test_index_returns_active_fortune_colors(): void
    {
        FortuneColor::factory()->count(3)->create(['is_active' => true]);
        FortuneColor::factory()->count(2)->inactive()->create();

        $res = $this->getJson('/api/v1/fortune-colors');

        $res->assertStatus(200)
            ->assertJsonPath('success', true);

        $res->assertJsonPath('data.0.is_active', true);
    }

    /**
     * POST /api/v1/fortune-colors/today (생년월일 기반 추천)
     *
     * @return void
     */
    public function test_today_returns_deterministic_color(): void
    {
        // id 정렬 기반으로 deterministic 선택됨
        FortuneColor::factory()->create(['is_active' => true]);
        FortuneColor::factory()->create(['is_active' => true]);
        FortuneColor::factory()->create(['is_active' => true]);

        $birthday = '2000-01-02';

        $active = FortuneColor::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->values();

        $seed = (int) str_replace('-', '', $birthday); // YYYYMMDD
        $expectedIndex = $seed % $active->count();
        $expectedId = $active->get($expectedIndex)->id;

        $res = $this->postJson('/api/v1/fortune-colors/today', [
            'birthday' => $birthday,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $expectedId);
    }

    /**
     * POST /api/v1/fortune-colors/today validation fail
     *
     * @return void
     */
    public function test_today_fails_validation_when_birthday_invalid(): void
    {
        $res = $this->postJson('/api/v1/fortune-colors/today', [
            'birthday' => '01-02-2000',
        ]);

        $res->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}