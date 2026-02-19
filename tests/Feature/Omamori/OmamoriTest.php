<?php

namespace Tests\Feature\Omamori;

use App\Models\FortuneColor;
use App\Models\Frame;
use App\Models\Omamori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OmamoriTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 오마모리 생성 (POST /api/v1/omamoris)
     */

    /**
     * 정상 오마모리 생성 테스트
     *
     * @return void
     */
    public function test_store_success(): void
    {
        $user = User::factory()->create();
        $color = FortuneColor::factory()->create();
        $frame = Frame::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/omamoris', [
            'title'                    => '합격 기원 오마모리',
            'meaning'                  => '시험 합격을 기원합니다',
            'theme'                    => 'academic',
            'size_code'                => 'medium',
            'applied_fortune_color_id' => $color->id,
            'applied_frame_id'         => $frame->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => '오마모리가 생성되었습니다.',
            ])
            ->assertJsonPath('data.title', '합격 기원 오마모리')
            ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('omamoris', [
            'user_id' => $user->id,
            'title'   => '합격 기원 오마모리',
            'status'  => 'draft',
        ]);
    }

    /**
     * 최소 필드(title만)로 생성 성공 테스트
     *
     * @return void
     */
    public function test_store_minimal_fields(): void
    {
        $user = User::factory()->create();
        $frame = Frame::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/omamoris', [
            'title' => '간단한 오마모리',
            'applied_frame_id' => $frame->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', '간단한 오마모리')
            ->assertJsonPath('data.status', 'draft');
    }

    /**
     * 제목 누락 시 생성 실패 테스트
     *
     * @return void
     */
    public function test_store_missing_title(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/omamoris', [
            'meaning' => '제목이 없습니다',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    /**
     * 제목 120자 초과 시 생성 실패 테스트
     *
     * @return void
     */
    public function test_store_title_too_long(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/omamoris', [
            'title' => str_repeat('가', 121),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    /**
     * 존재하지 않는 fortune_color_id로 생성 실패 테스트
     *
     * @return void
     */
    public function test_store_invalid_fortune_color(): void
    {
        $user = User::factory()->create();
        $frame = Frame::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/omamoris', [
            'title'                    => '테스트',
            'applied_fortune_color_id' => 99999,
            'applied_frame_id'         => $frame->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('applied_fortune_color_id');
    }

    /**
     * 존재하지 않는 frame_id로 생성 실패 테스트
     *
     * @return void
     */
    public function test_store_invalid_frame(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/omamoris', [
            'title'            => '테스트',
            'applied_frame_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('applied_frame_id');
    }

    /**
     * 인증 없이 생성 실패 테스트
     *
     * @return void
     */
    public function test_store_unauthorized(): void
    {
        $response = $this->postJson('/api/v1/omamoris', [
            'title' => '테스트',
        ]);

        $response->assertStatus(401);
    }

    /**
     * 오마모리 조회 (GET /api/v1/omamoris/{omamori})
     */

    /**
     * 본인 오마모리 조회 성공 테스트
     *
     * @return void
     */
    public function test_show_own_omamori(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()
            ->withFortuneColor()
            ->withFrame()
            ->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/omamoris/{$omamori->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.id', $omamori->id)
            ->assertJsonPath('data.title', $omamori->title)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'meaning', 'status',
                    'fortune_color' => ['id', 'code', 'name', 'hex'],
                    'frame' => ['id', 'name', 'frame_key'],
                ],
            ]);
    }

    /**
     * 타인의 published 오마모리 조회 성공 테스트
     *
     * @return void
     */
    public function test_show_others_published_omamori(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $omamori = Omamori::factory()->published()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($viewer);

        $response = $this->getJson("/api/v1/omamoris/{$omamori->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $omamori->id);
    }

    /**
     * 타인의 draft 오마모리 조회 실패 테스트 (403)
     *
     * @return void
     */
    public function test_show_others_draft_omamori_forbidden(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $omamori = Omamori::factory()->create([
            'user_id' => $owner->id,
            'status'  => 'draft',
        ]);

        Sanctum::actingAs($viewer);

        $response = $this->getJson("/api/v1/omamoris/{$omamori->id}");

        $response->assertStatus(403);
    }

    /**
     * 존재하지 않는 오마모리 조회 실패 테스트 (404)
     *
     * @return void
     */
    public function test_show_nonexistent_omamori(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/omamoris/99999');

        $response->assertStatus(404);
    }

    /**
     * 인증 없이 조회 실패 테스트
     *
     * @return void
     */
    public function test_show_unauthorized(): void
    {
        $omamori = Omamori::factory()->create();

        $response = $this->getJson("/api/v1/omamoris/{$omamori->id}");

        $response->assertStatus(401);
    }

    /**
     * 제작 정보 수정 (PATCH /api/v1/omamoris/{omamori})
     */

    /**
     * 제작 정보 수정 성공 테스트
     *
     * @return void
     */
    public function test_update_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->create([
            'user_id' => $user->id,
            'title'   => '원래 제목',
        ]);
        $newColor = FortuneColor::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}", [
            'title'                    => '수정된 제목',
            'applied_fortune_color_id' => $newColor->id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.title', '수정된 제목');

        $this->assertDatabaseHas('omamoris', [
            'id'    => $omamori->id,
            'title' => '수정된 제목',
            'applied_fortune_color_id' => $newColor->id,
        ]);
    }

    /**
     * 부분 수정 (title만) 성공 테스트
     *
     * @return void
     */
    public function test_update_partial(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->create([
            'user_id' => $user->id,
            'title'   => '원래 제목',
            'theme'   => 'love',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}", [
            'title' => '수정된 제목만',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', '수정된 제목만')
            ->assertJsonPath('data.theme', 'love'); // theme은 그대로
    }

    /**
     * 타인 오마모리 수정 실패 테스트 (403)
     *
     * @return void
     */
    public function test_update_others_omamori_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $omamori = Omamori::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($other);

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}", [
            'title' => '해킹 시도',
        ]);

        $response->assertStatus(403);
    }

    /**
     * 제목 120자 초과 수정 실패 테스트
     *
     * @return void
     */
    public function test_update_title_too_long(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}", [
            'title' => str_repeat('가', 121),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    /**
     * 인증 없이 수정 실패 테스트
     *
     * @return void
     */
    public function test_update_unauthorized(): void
    {
        $omamori = Omamori::factory()->create();

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}", [
            'title' => '테스트',
        ]);

        $response->assertStatus(401);
    }

    /**
     * 삭제 (DELETE /api/v1/omamoris/{omamori})
     */

    /**
     * 오마모리 삭제 성공 테스트 (소프트 삭제)
     *
     * @return void
     */
    public function test_destroy_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->create(['user_id' => $user->id]);
    
        Sanctum::actingAs($user);
    
        $response = $this->deleteJson("/api/v1/omamoris/{$omamori->id}");
    
        $response->assertStatus(204);
    
        $this->assertSoftDeleted('omamoris', [
            'id' => $omamori->id,
        ]);
    }

    /**
     * 타인 오마모리 삭제 실패 테스트 (403)
     *
     */
    public function test_destroy_others_omamori_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $omamori = Omamori::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($other);

        $response = $this->deleteJson("/api/v1/omamoris/{$omamori->id}");

        $response->assertStatus(403);
    }

    /**
     * 삭제 후 조회 실패 테스트 (404)
     *
     */
    public function test_show_after_delete_fails(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        // 삭제
        $this->deleteJson("/api/v1/omamoris/{$omamori->id}")
            ->assertStatus(204);

        // 조회 시도
        $response = $this->getJson("/api/v1/omamoris/{$omamori->id}");

        $response->assertStatus(404);
    }
    /**
     * 인증 없을 때 삭제 401 테스트
     *
     */
    public function test_destroy_unauthorized(): void
    {
        $omamori = Omamori::factory()->create();

        $response = $this->deleteJson("/api/v1/omamoris/{$omamori->id}");

        $response->assertStatus(401);
    }

    /**
     * 목록 조회 (GET /api/v1/omamoris)
     */

    /**
     * 내 오마모리 목록 조회 성공 테스트
     *
     * @return void
     */
    public function test_index_success(): void
    {
        $user = User::factory()->create();
        Omamori::factory()->count(3)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/omamoris');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'title', 'status']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    /**
     * 타인 오마모리는 목록에 안 보이는 테스트
     *
     * @return void
     */
    public function test_index_only_own(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Omamori::factory()->count(2)->create(['user_id' => $user->id]);
        Omamori::factory()->count(5)->create(['user_id' => $other->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/omamoris');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    /**
     * status 필터 테스트 (draft만)
     *
     * @return void
     */
    public function test_index_filter_by_status(): void
    {
        $user = User::factory()->create();
        Omamori::factory()->count(3)->create(['user_id' => $user->id, 'status' => 'draft']);
        Omamori::factory()->count(2)->published()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/omamoris?status=draft');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3);
    }

    /**
     * 페이지네이션 테스트
     *
     * @return void
     */
    public function test_index_pagination(): void
    {
        $user = User::factory()->create();
        Omamori::factory()->count(15)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/omamoris?size=5&page=2');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 15);
    }

    /**
     * 정렬 테스트 (oldest)
     *
     * @return void
     */
    public function test_index_sort_oldest(): void
    {
        $user = User::factory()->create();
        $first = Omamori::factory()->create([
            'user_id'    => $user->id,
            'title'      => '첫 번째',
            'created_at' => now()->subDays(2),
        ]);
        $second = Omamori::factory()->create([
            'user_id'    => $user->id,
            'title'      => '두 번째',
            'created_at' => now()->subDay(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/omamoris?sort=oldest');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($first->id, $data[0]['id']);
        $this->assertEquals($second->id, $data[1]['id']);
    }

    /**
     * 잘못된 status 값 실패 테스트
     *
     * @return void
     */
    public function test_index_invalid_status(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/omamoris?status=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    /**
     * 인증 없이 목록 조회 실패 테스트
     *
     * @return void
     */
    public function test_index_unauthorized(): void
    {
        $response = $this->getJson('/api/v1/omamoris');

        $response->assertStatus(401);
    }

    /**
     * 뒷면 메시지 (PATCH /api/v1/omamoris/{omamori}/back-message)
     */

    /**
     * 뒷면 메시지 입력 성공 테스트
     *
     * @return void
     */
    public function test_update_back_message_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}/back-message", [
            'back_message' => '항상 건강하세요!',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.back_message', '항상 건강하세요!');

        $this->assertDatabaseHas('omamoris', [
            'id'           => $omamori->id,
            'back_message' => '항상 건강하세요!',
        ]);
    }

    /**
     * 뒷면 메시지 수정 (기존 메시지 변경) 테스트
     *
     * @return void
     */
    public function test_update_back_message_overwrite(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->create([
            'user_id'      => $user->id,
            'back_message' => '기존 메시지',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}/back-message", [
            'back_message' => '새로운 메시지',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.back_message', '새로운 메시지');
    }

    /**
     * 타인 오마모리 뒷면 메시지 수정 실패 테스트 (403)
     *
     * @return void
     */
    public function test_update_back_message_others_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $omamori = Omamori::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($other);

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}/back-message", [
            'back_message' => '해킹 시도',
        ]);

        $response->assertStatus(403);
    }

    /**
     * 뒷면 메시지 누락 실패 테스트
     *
     * @return void
     */
    public function test_update_back_message_missing(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}/back-message", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('back_message');
    }

    /**
     * 뒷면 메시지 500자 초과 실패 테스트
     *
     * @return void
     */
    public function test_update_back_message_too_long(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}/back-message", [
            'back_message' => str_repeat('가', 501),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('back_message');
    }

    /**
     * 인증 없이 뒷면 메시지 수정 실패 테스트
     *
     * @return void
     */
    public function test_update_back_message_unauthorized(): void
    {
        $omamori = Omamori::factory()->create();

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}/back-message", [
            'back_message' => '테스트',
        ]);

        $response->assertStatus(401);
    }

    public function test_store_without_frame_applies_default_frame(): void
{
    $user = User::factory()->create();

    $defaultFrame = Frame::factory()->default()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/omamoris', [
        'title' => '기본 프레임 자동 적용',
        // applied_frame_id intentionally omitted
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.title', '기본 프레임 자동 적용')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.frame.id', $defaultFrame->id);

    $this->assertDatabaseHas('omamoris', [
        'user_id' => $user->id,
        'title' => '기본 프레임 자동 적용',
        'applied_frame_id' => $defaultFrame->id,
    ]);
}

}