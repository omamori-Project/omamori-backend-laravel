<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // 회원가입

    /**
     * 정상 회원가입 테스트
     *
     * @return void
     */
    public function test_register_success(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'name' => '테스트유저',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'email', 'name', 'role', 'is_active'],
                    'token',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'test@example.com',
                        'name' => '테스트유저',
                    ],
                ],
            ]);

        // DB 확인
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => '테스트유저',
        ]);

        // Local Identity 생성 확인
        $this->assertDatabaseHas('user_identities', [
            'provider' => 'local',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * 중복 이메일 회원가입 실패 테스트
     *
     * @return void
     */
    public function test_register_duplicate_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'name' => '중복유저',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * 비밀번호 확인 불일치 실패 테스트
     *
     * @return void
     */
    public function test_register_password_mismatch(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpassword',
            'name' => '테스트유저',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * 필수 필드 누락 회원가입 실패 테스트
     *
     * @return void
     */
    public function test_register_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'name']);
    }

    /**
     * 비밀번호 8자 미만 실패 테스트
     *
     * @return void
     */
    public function test_register_short_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
            'name' => '테스트유저',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    // 로그인

    /**
     * 정상 로그인 테스트
     *
     * @return void
     */
    public function test_login_success(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        UserIdentity::factory()->local($user)->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'email', 'name'],
                    'token',
                ],
            ])
            ->assertJson(['success' => true]);
    }

    /**
     * 잘못된 비밀번호 로그인 실패 테스트
     *
     * @return void
     */
    public function test_login_wrong_password(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        UserIdentity::factory()->local($user)->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * 존재하지 않는 이메일 로그인 실패 테스트
     *
     * @return void
     */
    public function test_login_nonexistent_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * 비활성 계정 로그인 실패 테스트
     *
     * @return void
     */
    public function test_login_inactive_account(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => false,
        ]);
        UserIdentity::factory()->local($user)->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    // 로그아웃

    /**
     * 정상 로그아웃 테스트
     *
     * @return void
     */
    public function test_logout_success(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '로그아웃 완료',
            ]);

        // 토큰 삭제 확인
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * 인증 없이 로그아웃 실패 테스트
     *
     * @return void
     */
    public function test_logout_without_token(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    /**
     * 로그아웃 후 토큰 사용 불가 테스트
     *
     * @return void
     */
    public function test_token_invalid_after_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;
    
        // 로그아웃
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(200);
    
        // 인증 캐시 초기화
        $this->refreshApplication();
    
        // 같은 토큰으로 접근 시도
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me')
            ->assertStatus(401);
    }
}