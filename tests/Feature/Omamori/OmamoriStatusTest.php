<?php

namespace Tests\Feature\Omamori;

use App\Models\Frame;
use App\Models\FortuneColor;
use App\Models\Omamori;
use App\Models\OmamoriElement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OmamoriStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 임시저장 성공 (POST /api/v1/omamoris/{id}/save-draft)
     * - 이미 draft면 그대로 OK (idempotent)
     *
     * @return void
     */
    public function test_save_draft_success_when_already_draft(): void
    {
        $user = User::factory()->create();
        $frame = Frame::factory()->create();

        $omamori = Omamori::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'published_at' => null,
            'applied_frame_id' => $frame->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/save-draft");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.published_at', null);

        $this->assertDatabaseHas('omamoris', [
            'id' => $omamori->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * 임시저장 실패: 타인 오마모리 (403)
     *
     * @return void
     */
    public function test_save_draft_others_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $frame = Frame::factory()->create();

        $omamori = Omamori::factory()->create([
            'user_id' => $owner->id,
            'status' => 'draft',
            'applied_frame_id' => $frame->id,
        ]);

        Sanctum::actingAs($other);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/save-draft");

        $response->assertStatus(403);
    }

    /**
     * 임시저장 실패: 인증 없음 (401)
     *
     * @return void
     */
    public function test_save_draft_unauthorized(): void
    {
        $frame = Frame::factory()->create();

        $omamori = Omamori::factory()->create([
            'status' => 'draft',
            'applied_frame_id' => $frame->id,
        ]);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/save-draft");

        $response->assertStatus(401);
    }

    /**
     * 임시저장 성공: published → draft (POST /api/v1/omamoris/{id}/save-draft)
     *
     * @return void
     */
    public function test_save_draft_conflict_when_published(): void
    {
        $user = User::factory()->create();
        $frame = Frame::factory()->create();
    
        $omamori = Omamori::factory()->create([
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
            'applied_frame_id' => $frame->id,
        ]);
    
        Sanctum::actingAs($user);
    
        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/save-draft");
    
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.published_at', null);
    
        $this->assertDatabaseHas('omamoris', [
            'id' => $omamori->id,
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * 발행 성공 (POST /api/v1/omamoris/{id}/publish)
     * - draft -> published
     * - published_at 세팅
     *
     * @return void
     */
    public function test_publish_success(): void
    {
        $user = User::factory()->create();

        $fortuneColor = FortuneColor::factory()->create();
        $frame = Frame::factory()->create();

        $omamori = Omamori::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'published_at' => null,
            'applied_fortune_color_id' => $fortuneColor->id,
            'applied_frame_id' => $frame->id,
        ]);

        // background 제외 요소 1개 이상 필요 (text/stamp)
        OmamoriElement::factory()->create([
            'omamori_id' => $omamori->id,
            'type' => 'text',
            'layer' => 1,
            'props' => ['text' => '발행 테스트'],
            'transform' => ['x' => 0, 'y' => 0, 'scale' => 1, 'rotate' => 0],
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/publish");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.published_at', fn ($v) => is_string($v) && $v !== '');

        $this->assertDatabaseHas('omamoris', [
            'id' => $omamori->id,
            'user_id' => $user->id,
            'status' => 'published',
        ]);

        $this->assertNotNull($omamori->fresh()->published_at);
    }

    /**
     * 발행 idempotent: 이미 published면 그대로 OK (200)
     *
     * @return void
     */
    public function test_publish_idempotent_when_already_published(): void
    {
        $user = User::factory()->create();
        $frame = Frame::factory()->create();

        $omamori = Omamori::factory()->create([
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
            'applied_frame_id' => $frame->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/publish");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.published_at', fn ($v) => is_string($v) && $v !== '');
    }

    /**
     * 발행 실패: background 제외 요소가 없으면 422
     * - 요소가 아예 없거나
     * - background만 존재하는 경우
     *
     * @return void
     */
    public function test_publish_should_422_when_no_non_background_elements(): void
    {
        $user = User::factory()->create();

        $fortuneColor = FortuneColor::factory()->create();
        $frame = Frame::factory()->create();

        $omamori = Omamori::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'applied_fortune_color_id' => $fortuneColor->id,
            'applied_frame_id' => $frame->id,
        ]);

        // background만 추가
        OmamoriElement::factory()->create([
            'omamori_id' => $omamori->id,
            'type' => 'background',
            'layer' => 0,
            'props' => ['kind' => 'solid', 'value' => '#ffffff'],
            'transform' => ['x' => 0, 'y' => 0, 'scale' => 1, 'rotate' => 0],
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/publish");

        $response->assertStatus(422)
            ->assertJsonValidationErrors('elements');
    }

    /**
     * 발행 실패: 필수 메타 누락 시 422
     * - applied_fortune_color_id
     *
     * @return void
     */
    public function test_publish_should_422_when_required_meta_missing(): void
    {
        $user = User::factory()->create();
        $frame = Frame::factory()->create();

        $omamori = Omamori::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'applied_fortune_color_id' => null,
            'applied_frame_id' => $frame->id,
        ]);

        // 요소는 충족
        OmamoriElement::factory()->create([
            'omamori_id' => $omamori->id,
            'type' => 'stamp',
            'layer' => 1,
            'props' => ['key' => 'heart'],
            'transform' => ['x' => 0, 'y' => 0, 'scale' => 1, 'rotate' => 0],
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/publish");

        $response->assertStatus(422)
            ->assertJsonValidationErrors('applied_fortune_color_id');
    }

    /**
     * 발행 실패: 타인 오마모리 (403)
     *
     * @return void
     */
    public function test_publish_others_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $fortuneColor = FortuneColor::factory()->create();
        $frame = Frame::factory()->create();

        $omamori = Omamori::factory()->create([
            'user_id' => $owner->id,
            'status' => 'draft',
            'applied_fortune_color_id' => $fortuneColor->id,
            'applied_frame_id' => $frame->id,
        ]);

        OmamoriElement::factory()->create([
            'omamori_id' => $omamori->id,
            'type' => 'text',
            'layer' => 1,
            'props' => ['text' => '권한 테스트'],
            'transform' => ['x' => 0, 'y' => 0, 'scale' => 1, 'rotate' => 0],
        ]);

        Sanctum::actingAs($other);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/publish");

        $response->assertStatus(403);
    }

    /**
     * 발행 실패: 인증 없음 (401)
     *
     * @return void
     */
    public function test_publish_unauthorized(): void
    {
        $frame = Frame::factory()->create();

        $omamori = Omamori::factory()->create([
            'status' => 'draft',
            'applied_frame_id' => $frame->id,
        ]);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/publish");

        $response->assertStatus(401);
    }
}