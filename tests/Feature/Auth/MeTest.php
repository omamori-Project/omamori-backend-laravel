<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    // 내 정보 조회 

    /**
     * 내 정보 조회 성공 테스트
     *
     * @return void
     */
    public function test_show_profile_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => '테스트유저',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => 'test@example.com',
                    'name' => '테스트유저',
                ],
            ]);
    }

    /**
     * 인증 없이 내 정보 조회 실패 테스트
     *
     * @return void
     */
    public function test_show_profile_unauthorized(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    // 회원 정보 수정

    /**
     * 이름 수정 성공 테스트
     *
     * @return void
     */
    public function test_update_name_success(): void
    {
        $user = User::factory()->create(['name' => '원래이름']);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/me', [
            'name' => '수정된이름',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['name' => '수정된이름'],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '수정된이름',
        ]);
    }

    /**
     * 비밀번호 변경 성공 테스트
     *
     * @return void
     */
    public function test_update_password_success(): void
    {
        $user = User::factory()->create();
        $identity = UserIdentity::factory()->local($user)->create();

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/me', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // 변경된 비밀번호로 로그인 확인
        $identity->refresh();
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('newpassword123', $identity->password_hash)
        );
    }

    /**
     * 이름과 비밀번호 동시 수정 테스트
     *
     * @return void
     */
    public function test_update_name_and_password(): void
    {
        $user = User::factory()->create(['name' => '원래이름']);
        UserIdentity::factory()->local($user)->create();

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/me', [
            'name' => '새이름',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['name' => '새이름'],
            ]);
    }

    /**
     * 짧은 비밀번호 수정 실패 테스트
     *
     * @return void
     */
    public function test_update_short_password_fails(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/me', [
            'password' => '1234',
            'password_confirmation' => '1234',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * 이름 100자 초과 실패 테스트
     *
     * @return void
     */
    public function test_update_long_name_fails(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/me', [
            'name' => str_repeat('가', 101),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    // 로그인 수단 목록 

    /**
     * 로그인 수단 목록 조회 성공 테스트
     *
     * @return void
     */
    public function test_identities_list_success(): void
    {
        $user = User::factory()->create();
        UserIdentity::factory()->local($user)->create();
        UserIdentity::factory()->google($user)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me/identities');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(2, 'data');
    }

    /**
     * Local만 있는 경우 로그인 수단 목록 테스트
     *
     * @return void
     */
    public function test_identities_local_only(): void
    {
        $user = User::factory()->create();
        UserIdentity::factory()->local($user)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me/identities');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['provider' => 'local']);
    }

    // 회원 탈퇴 

    /**
     * 회원 탈퇴 성공 테스트
     *
     * @return void
     */
    public function test_delete_account_success(): void
    {
        $user = User::factory()->create(['email' => 'delete@example.com']);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '회원 탈퇴 완료',
            ]);

        // 소프트 삭제 확인
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);

        // 토큰 삭제 확인
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * 탈퇴 후 로그인 불가 테스트
     *
     * @return void
     */
    public function test_login_after_delete_fails(): void
    {
        $user = User::factory()->create(['email' => 'delete@example.com']);
        UserIdentity::factory()->local($user)->create();

        Sanctum::actingAs($user);

        // 탈퇴
        $this->deleteJson('/api/v1/me')->assertStatus(200);

        // 로그인 시도
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'delete@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    /**
     * 인증 없이 회원 탈퇴 실패 테스트
     *
     * @return void
     */
    public function test_delete_account_unauthorized(): void
    {
        $response = $this->deleteJson('/api/v1/me');

        $response->assertStatus(401);
    }
}