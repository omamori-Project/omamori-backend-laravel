<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserIdentityFactory extends Factory
{
    protected $model = UserIdentity::class;

    /**
     * 기본 Identity 정의
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => 'local',
            'provider_user_id' => fake()->unique()->numerify('####'),
            'email' => fake()->safeEmail(),
            'password_hash' => null,
            'profile' => [],
            'linked_at' => now(),
            'last_used_at' => null,
            'revoked_at' => null,
        ];
    }

    /**
     * Local 로그인 수단
     *
     * @param User $user
     * @param string $password
     * @return static
     */
    public function local(User $user, string $password = 'password123'): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'provider' => 'local',
            'provider_user_id' => $user->id,
            'email' => $user->email,
            'password_hash' => Hash::make($password),
            'profile' => [],
        ]);
    }

    /**
     * Google 로그인 수단
     *
     * @param User $user
     * @param string|null $providerUserId
     * @return static
     */
    public function google(User $user, ?string $providerUserId = null): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => $providerUserId ?? 'google-' . fake()->unique()->numerify('######'),
            'email' => $user->email,
            'password_hash' => null,
            'profile' => [
                'name' => $user->name,
                'avatar' => 'https://avatar.example.com/photo.jpg',
            ],
        ]);
    }
}