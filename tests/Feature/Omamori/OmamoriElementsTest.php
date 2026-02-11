<?php

namespace Tests\Feature\Omamori;

use App\Models\Omamori;
use App\Models\OmamoriElement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OmamoriElementsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * stamp 생성 성공 (asset_key 저장)
     *
     * @return void
     */
    public function test_store_stamp_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/elements", [
            'type' => 'stamp',
            'props' => [
                'asset_key' => 'heart',
            ],
            'transform' => [
                'x' => 100,
                'y' => 200,
                'scale' => 1.2,
                'rotate' => 15,
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'stamp')
            ->assertJsonPath('data.props.asset_key', 'heart')
            ->assertJsonPath('data.transform.x', 100);

        $this->assertDatabaseHas('omamori_elements', [
            'omamori_id' => $omamori->id,
            'type' => 'stamp',
        ]);
    }

    /**
     * stamp 생성 시 asset_key 누락이면 422
     * (StoreElementRequest에 props.asset_key required 추가되어 있어야 함)
     *
     * @return void
     */
    public function test_store_stamp_missing_asset_key_should_422(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/elements", [
            'type' => 'stamp',
            'props' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('props.asset_key');
    }

    /**
     * background 업서트: 2번 호출해도 1개만 존재 + layer=0 고정
     *
     * @return void
     */
    public function test_store_background_upsert_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $res1 = $this->postJson("/api/v1/omamoris/{$omamori->id}/elements", [
            'type' => 'background',
            'props' => [
                'kind' => 'solid',
                'color' => '#ffffff',
            ],
        ]);

        $res1->assertStatus(201)
            ->assertJsonPath('data.type', 'background')
            ->assertJsonPath('data.layer', 0)
            ->assertJsonPath('data.props.kind', 'solid');

        $res2 = $this->postJson("/api/v1/omamoris/{$omamori->id}/elements", [
            'type' => 'background',
            'props' => [
                'kind' => 'solid',
                'color' => '#000000',
            ],
        ]);

        $res2->assertStatus(201)
            ->assertJsonPath('data.type', 'background')
            ->assertJsonPath('data.layer', 0)
            ->assertJsonPath('data.props.color', '#000000');

        $this->assertSame(
            1,
            OmamoriElement::query()
                ->where('omamori_id', $omamori->id)
                ->where('type', 'background')
                ->count()
        );
    }

    /**
     * 요소 수정 성공 (props/transform)
     *
     * @return void
     */
    public function test_update_element_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create();

        $element = OmamoriElement::factory()
            ->forOmamori($omamori)
            ->stamp('heart')
            ->state(['layer' => 1])
            ->create();

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/omamoris/{$omamori->id}/elements/{$element->id}", [
            'props' => ['asset_key' => 'star'],
            'transform' => ['x' => 111, 'y' => 222],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $element->id)
            ->assertJsonPath('data.type', 'stamp')
            ->assertJsonPath('data.props.asset_key', 'star')
            ->assertJsonPath('data.transform.x', 111);

        $this->assertDatabaseHas('omamori_elements', [
            'id' => $element->id,
        ]);
    }

    /**
     * 요소 삭제 성공 (Soft Delete)
     *
     * @return void
     */
    public function test_destroy_element_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create();

        $element = OmamoriElement::factory()
            ->forOmamori($omamori)
            ->stamp('heart')
            ->state(['layer' => 1])
            ->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/omamoris/{$omamori->id}/elements/{$element->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('omamori_elements', [
            'id' => $element->id,
        ]);
    }

    /**
     * reorder 성공: background 제외 + non-background 전체 ID 일치 + layer=1..n 재할당
     *
     * @return void
     */
    public function test_reorder_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create();

        $bg = OmamoriElement::factory()
            ->forOmamori($omamori)
            ->background()
            ->create();

        $e1 = OmamoriElement::factory()->forOmamori($omamori)->stamp('a')->state(['layer' => 1])->create();
        $e2 = OmamoriElement::factory()->forOmamori($omamori)->text('hello')->state(['layer' => 2])->create();
        $e3 = OmamoriElement::factory()->forOmamori($omamori)->stamp('b')->state(['layer' => 3])->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/elements/reorder", [
            'elementIds' => [$e3->id, $e1->id, $e2->id],
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('omamori_elements', ['id' => $bg->id, 'layer' => 0]);
        $this->assertDatabaseHas('omamori_elements', ['id' => $e3->id, 'layer' => 1]);
        $this->assertDatabaseHas('omamori_elements', ['id' => $e1->id, 'layer' => 2]);
        $this->assertDatabaseHas('omamori_elements', ['id' => $e2->id, 'layer' => 3]);
    }

    /**
     * reorder에 background 포함하면 422
     *
     * @return void
     */
    public function test_reorder_with_background_should_422(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create();

        $bg = OmamoriElement::factory()->forOmamori($omamori)->background()->create();
        $e1 = OmamoriElement::factory()->forOmamori($omamori)->stamp('a')->state(['layer' => 1])->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/elements/reorder", [
            'elementIds' => [$bg->id, $e1->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('elementIds');
    }

    /**
     * reorder에 non-background 전체 ID가 누락되면 422
     *
     * @return void
     */
    public function test_reorder_missing_non_background_should_422(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create();

        $e1 = OmamoriElement::factory()->forOmamori($omamori)->stamp('a')->state(['layer' => 1])->create();
        $e2 = OmamoriElement::factory()->forOmamori($omamori)->text('hello')->state(['layer' => 2])->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/omamoris/{$omamori->id}/elements/reorder", [
            'elementIds' => [$e1->id], // e2 누락
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('elementIds');
    }

    /**
     * 타인 오마모리 접근은 403 (update/destroy/reorder)
     *
     * @return void
     */
    public function test_other_user_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $omamori = Omamori::factory()->for($owner)->create();

        $element = OmamoriElement::factory()
            ->forOmamori($omamori)
            ->stamp('a')
            ->state(['layer' => 1])
            ->create();

        Sanctum::actingAs($other);

        $res1 = $this->patchJson("/api/v1/omamoris/{$omamori->id}/elements/{$element->id}", [
            'props' => ['asset_key' => 'b'],
        ]);
        $res1->assertStatus(403);

        $res2 = $this->deleteJson("/api/v1/omamoris/{$omamori->id}/elements/{$element->id}");
        $res2->assertStatus(403);

        $res3 = $this->postJson("/api/v1/omamoris/{$omamori->id}/elements/reorder", [
            'elementIds' => [$element->id],
        ]);
        $res3->assertStatus(403);
    }
}