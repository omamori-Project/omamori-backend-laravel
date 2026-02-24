<?php

namespace Tests\Feature\Stamp;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StampTest extends TestCase
{
    /**
     * 테스트 환경 초기 설정
     *
     * - Sanctum 인증 사용자 생성
     * - 기본 파일시스템 디스크를 public으로 고정
     * - public 디스크를 fake 처리
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 인증 사용자 생성 및 로그인 처리
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Repository에서 사용하는 기본 디스크 고정
        Config::set('filesystems.default', 'public');

        // 스토리지 가짜 처리
        Storage::fake('public');
    }

    /**
     * 스탬프 목록 조회 성공.
     *
     * - 기본 요청 시 200 OK 반환
     * - success = true
     * - paginator 구조 반환
     *
     * @return void
     */
    public function test_index_success_returns_paginated_items(): void
    {
        Storage::disk('public')->put('stamps/1.png', 'x');
        Storage::disk('public')->put('stamps/2.png', 'x');
        Storage::disk('public')->put('stamps/10.png', 'x');

        $response = $this->getJson('/api/v1/stamps');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertIsArray($response->json('data.data'));
        $this->assertNotEmpty($response->json('data.data'));

        $first = $response->json('data.data.0');

        $this->assertArrayHasKey('asset_key', $first);
        $this->assertArrayHasKey('file_name', $first);
        $this->assertArrayHasKey('url', $first);
    }

    /**
     * q 파라미터로 파일명 부분 검색이 동작
     *
     * @return void
     */
    public function test_index_filters_by_q(): void
    {
        Storage::disk('public')->put('stamps/1.png', 'x');
        Storage::disk('public')->put('stamps/12.png', 'x');
        Storage::disk('public')->put('stamps/99.png', 'x');

        $response = $this->getJson('/api/v1/stamps?q=1');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $items = $response->json('data.data');

        $assetKeys = array_map(
            fn (array $item): string => (string) $item['asset_key'],
            $items
        );

        $this->assertContains('1', $assetKeys);
        $this->assertContains('12', $assetKeys);
        $this->assertNotContains('99', $assetKeys);
    }

    /**
     * ext 파라미터로 확장자 필터링이 동작
     *
     * @return void
     */
    public function test_index_filters_by_ext(): void
    {
        Storage::disk('public')->put('stamps/1.png', 'x');
        Storage::disk('public')->put('stamps/2.jpg', 'x');
        Storage::disk('public')->put('stamps/3.jpeg', 'x');

        $response = $this->getJson('/api/v1/stamps?ext=png');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $items = $response->json('data.data');

        $fileNames = array_map(
            fn (array $item): string => (string) $item['file_name'],
            $items
        );

        $this->assertContains('1.png', $fileNames);
        $this->assertNotContains('2.jpg', $fileNames);
        $this->assertNotContains('3.jpeg', $fileNames);
    }

    /**
     * page / size 파라미터로 페이징이 정상 동작
     *
     * @return void
     */
    public function test_index_paginates_with_page_and_size(): void
    {
        // given
        Storage::disk('public')->put('stamps/1.png', 'x');
        Storage::disk('public')->put('stamps/2.png', 'x');
        Storage::disk('public')->put('stamps/3.png', 'x');
        Storage::disk('public')->put('stamps/4.png', 'x');
        Storage::disk('public')->put('stamps/5.png', 'x');

        $response = $this->getJson('/api/v1/stamps?page=2&size=2');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.current_page', 2)
            ->assertJsonPath('data.per_page', 2);

        $this->assertCount(2, $response->json('data.data'));
        $this->assertSame(5, (int) $response->json('data.total'));
    }
}