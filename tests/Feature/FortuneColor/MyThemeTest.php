<?php

declare(strict_types=1);

namespace Tests\Feature\FortuneColor;

use App\Models\FortuneColor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MyThemeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PATCH /api/v1/me/theme 적용 성공
     *
     * @return void
     */
    public function test_update_theme_applies_fortune_color(): void
    {
        $user = User::factory()->create();
        $color = FortuneColor::factory()->create(['is_active' => true]);

        Sanctum::actingAs($user);

        $res = $this->patchJson('/api/v1/me/theme', [
            'fortuneColorId' => $color->id,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.applied_fortune_color_id', $color->id);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'applied_fortune_color_id' => $color->id,
        ]);
    }

    /**
     * PATCH /api/v1/me/theme 해제(null)
     *
     * @return void
     */
    public function test_update_theme_detaches_when_null(): void
    {
        $user = User::factory()->create([
            'applied_fortune_color_id' => null,
        ]);

        $color = FortuneColor::factory()->create(['is_active' => true]);

        // 먼저 적용
        $user->update(['applied_fortune_color_id' => $color->id]);

        Sanctum::actingAs($user);

        $res = $this->patchJson('/api/v1/me/theme', [
            'fortuneColorId' => null,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.applied_fortune_color_id', null);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'applied_fortune_color_id' => null,
        ]);
    }

    /**
     * 비활성 컬러 적용은 422 (서비스에서 막음)
     *
     * @return void
     */
    public function test_update_theme_fails_when_inactive_color(): void
    {
        $user = User::factory()->create();
        $inactive = FortuneColor::factory()->inactive()->create();

        Sanctum::actingAs($user);

        $res = $this->patchJson('/api/v1/me/theme', [
            'fortuneColorId' => $inactive->id,
        ]);

        $res->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}