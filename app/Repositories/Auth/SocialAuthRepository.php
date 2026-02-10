<?php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Models\UserIdentity;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class SocialAuthRepository extends BaseRepository
{
    /**
     * UserIdentity 모델 인스턴스 반환
     *
     * @return Model
     */
    protected function getModel(): Model
    {
        return new UserIdentity();
    }

    /**
     * provider + provider_user_id로 Identity 조회
     *
     * @param string $provider
     * @param string $providerUserId
     * @return UserIdentity|null
     */
    public function findByProvider(string $provider, string $providerUserId): ?UserIdentity
    {
        return UserIdentity::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->whereNull('revoked_at')
            ->first();
    }

    /**
     * 특정 유저의 특정 provider Identity 조회
     *
     * @param int $userId
     * @param string $provider
     * @return UserIdentity|null
     */
    public function findUserIdentity(int $userId, string $provider): ?UserIdentity
    {
        return UserIdentity::where('user_id', $userId)
            ->where('provider', $provider)
            ->whereNull('revoked_at')
            ->first();
    }

    /**
     * 유저에게 소셜 Identity 생성
     *
     * @param User $user
     * @param array $data
     * @return UserIdentity
     */
    public function createIdentity(User $user, array $data): UserIdentity
    {
        return $user->identities()->create($data);
    }

    /**
     * Identity 연결 해제 (soft revoke)
     *
     * @param UserIdentity $identity
     * @return void
     */
    public function revokeIdentity(UserIdentity $identity): void
    {
        $identity->update(['revoked_at' => now()]);
    }

    /**
     * 유저의 활성 로그인 수단 개수 조회
     *
     * @param int $userId
     * @return int
     */
    public function countActiveIdentities(int $userId): int
    {
        return UserIdentity::where('user_id', $userId)
            ->whereNull('revoked_at')
            ->count();
    }
}