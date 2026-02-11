<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Socialite Mock 유저 생성 헬퍼
     *
     * @param array $overrides
     * @return SocialiteUser
     */
    private function mockSocialiteUser(array $overrides = []): SocialiteUser
    {
        $user = new SocialiteUser();
        $user->id = $overrides['id'] ?? 'google-123456';
        $user->name = $overrides['name'] ?? 'Google유저';
        $user->email = $overrides['email'] ?? 'google@example.com';
        $user->avatar = $overrides['avatar'] ?? 'https://avatar.example.com/photo.jpg';
        $user->token = $overrides['token'] ?? 'mock-access-token';

        return $user;
    }

    /**
     * Socialite driver Mock 설정 헬퍼
     *
     * @param SocialiteUser $socialiteUser
     * @param string $method (user | userFromToken)
     * @return void
     */
    private function mockSocialiteDriver(SocialiteUser $socialiteUser, string $method = 'user'): void
    {
        $driver = Mockery::mock(GoogleProvider::class);

        $driver->shouldReceive('stateless')->andReturnSelf();
        $driver->shouldReceive($method)->andReturn($socialiteUser);

        if ($method === 'user') {
            $driver->shouldReceive('redirect->getTargetUrl')
                ->andReturn('https://accounts.google.com/o/oauth2/auth?mock=true');
        }

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($driver);
    }

    // OAuth 리다이렉트

    /**
     * Google OAuth 리다이렉트 URL 반환 테스트
     *
     * @return void
     */
    public function test_google_redirect_returns_url(): void
    {
        $this->mockSocialiteDriver($this->mockSocialiteUser());

        $response = $this->getJson('/api/v1/auth/google');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['redirect_url']]);
    }

    // OAuth 콜백

    /**
     * 신규 유저 Google 로그인 테스트
     *
     * @return void
     */
    public function test_google_callback_new_user(): void
    {
        $socialiteUser = $this->mockSocialiteUser();
        $this->mockSocialiteDriver($socialiteUser);

        $response = $this->getJson('/api/v1/auth/google/callback');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'google@example.com',
                        'name' => 'Google유저',
                    ],
                ],
            ])
            ->assertJsonStructure(['data' => ['token']]);

        // DB 확인
        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
        ]);

        $this->assertDatabaseHas('user_identities', [
            'provider' => 'google',
            'provider_user_id' => 'google-123456',
        ]);
    }

    /**
     * 기존 이메일 유저가 Google 로그인 시 Identity 연결 테스트
     *
     * @return void
     */
    public function test_google_callback_existing_email_user(): void
    {
        $user = User::factory()->create(['email' => 'google@example.com']);

        $socialiteUser = $this->mockSocialiteUser(['email' => 'google@example.com']);
        $this->mockSocialiteDriver($socialiteUser);

        $response = $this->getJson('/api/v1/auth/google/callback');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // 기존 유저에 Identity 연결 확인
        $this->assertDatabaseHas('user_identities', [
            'user_id' => $user->id,
            'provider' => 'google',
        ]);

        // 유저 중복 생성 안됨 확인
        $this->assertDatabaseCount('users', 1);
    }

    /**
     * 이미 Google 연결된 유저 재로그인 테스트
     *
     * @return void
     */
    public function test_google_callback_returning_user(): void
    {
        $user = User::factory()->create(['email' => 'google@example.com']);
        UserIdentity::factory()->google($user, 'google-123456')->create();

        $socialiteUser = $this->mockSocialiteUser();
        $this->mockSocialiteDriver($socialiteUser);

        $response = $this->getJson('/api/v1/auth/google/callback');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Identity 중복 생성 안됨 확인
        $this->assertDatabaseCount('user_identities', 1);
    }

    // 계정 연결 

    /**
     * Google 계정 연결 성공 테스트
     *
     * @return void
     */
    public function test_google_link_success(): void
    {
        $user = User::factory()->create();
        UserIdentity::factory()->local($user)->create();

        Sanctum::actingAs($user);

        $socialiteUser = $this->mockSocialiteUser();
        $this->mockSocialiteDriver($socialiteUser, 'userFromToken');

        $response = $this->postJson('/api/v1/auth/google/link', [
            'access_token' => 'mock-access-token',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Google 계정 연결 완료',
            ]);

        $this->assertDatabaseHas('user_identities', [
            'user_id' => $user->id,
            'provider' => 'google',
        ]);
    }

    /**
     * 이미 다른 계정에 연결된 Google 계정 연결 실패 테스트
     *
     * @return void
     */
    public function test_google_link_already_linked_to_other(): void
    {
        $otherUser = User::factory()->create();
        UserIdentity::factory()->google($otherUser, 'google-123456')->create();

        $user = User::factory()->create();
        UserIdentity::factory()->local($user)->create();

        Sanctum::actingAs($user);

        $socialiteUser = $this->mockSocialiteUser(['id' => 'google-123456']);
        $this->mockSocialiteDriver($socialiteUser, 'userFromToken');

        $response = $this->postJson('/api/v1/auth/google/link', [
            'access_token' => 'mock-access-token',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => '이 소셜 계정은 이미 다른 계정에 연결되어 있습니다.',
            ]);
    }

    /**
     * 이미 본인이 Google 연결한 경우 중복 연결 실패 테스트
     *
     * @return void
     */
    public function test_google_link_already_linked_to_self(): void
    {
        $user = User::factory()->create();
        UserIdentity::factory()->local($user)->create();
        UserIdentity::factory()->google($user, 'google-999')->create();

        Sanctum::actingAs($user);

        $socialiteUser = $this->mockSocialiteUser(['id' => 'google-new']);
        $this->mockSocialiteDriver($socialiteUser, 'userFromToken');

        $response = $this->postJson('/api/v1/auth/google/link', [
            'access_token' => 'mock-access-token',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => '이미 연결된 소셜 계정입니다.',
            ]);
    }

    /**
     * access_token 누락 시 연결 실패 테스트
     *
     * @return void
     */
    public function test_google_link_missing_token(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/google/link', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('access_token');
    }

    //계정 연결 해제 

    /**
     * Google 연결 해제 성공 테스트 (2개 이상 수단 보유)
     *
     * @return void
     */
    public function test_google_unlink_success(): void
    {
        $user = User::factory()->create();
        UserIdentity::factory()->local($user)->create();
        UserIdentity::factory()->google($user)->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/auth/google/unlink');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Google 계정 연결 해제 완료',
            ]);

        // revoked_at이 설정되었는지 확인
        $this->assertDatabaseHas('user_identities', [
            'user_id' => $user->id,
            'provider' => 'google',
        ]);

        $identity = UserIdentity::where('user_id', $user->id)
            ->where('provider', 'google')
            ->first();

        $this->assertNotNull($identity->revoked_at);
    }

    /**
     * 로그인 수단 1개뿐일 때 연결 해제 실패 테스트
     *
     * @return void
     */
    public function test_google_unlink_last_identity_fails(): void
    {
        $user = User::factory()->create();
        UserIdentity::factory()->google($user)->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/auth/google/unlink');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => '최소 1개의 로그인 수단이 필요합니다.',
            ]);
    }

    /**
     * Google 연결 없는데 해제 시도 실패 테스트
     *
     * @return void
     */
    public function test_google_unlink_not_linked(): void
    {
        $user = User::factory()->create();
        UserIdentity::factory()->local($user)->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/auth/google/unlink');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => '연결된 계정을 찾을 수 없습니다.',
            ]);
    }

    /**
     * 인증 없이 연결 해제 실패 테스트
     *
     * @return void
     */
    public function test_google_unlink_unauthorized(): void
    {
        $response = $this->deleteJson('/api/v1/auth/google/unlink');

        $response->assertStatus(401);
    }
}