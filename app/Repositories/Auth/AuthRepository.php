<?php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Models\UserIdentity;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AuthRepository extends BaseRepository
{
    /**
     * User 모델 인스턴스 반환
     *
     * @return Model
     */
    protected function getModel(): Model
    {
        return new User();
    }

    /**
     * 이메일로 유저 조회
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * 유저의 로그인 수단(Identity) 생성
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
     * 유저의 Local 로그인 수단 조회
     *
     * @param int $userId
     * @return UserIdentity|null
     */
    public function findLocalIdentity(int $userId): ?UserIdentity
    {
        return UserIdentity::where('user_id', $userId)
            ->where('provider', 'local')
            ->whereNull('revoked_at')
            ->first();
    }

    /**
     * 마지막 로그인 시각 갱신
     *
     * @param User $user
     * @return void
     */
    public function updateLastLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }

    /**
     * Identity 마지막 사용 시각 갱신
     *
     * @param UserIdentity $identity
     * @return void
     */
    public function updateLastUsed(UserIdentity $identity): void
    {
        $identity->update(['last_used_at' => now()]);
    }

    /**
     * 유저의 활성 로그인 수단 목록 조회
     *
     * @param int $userId
     * @return Collection
     */
    public function getActiveIdentities(int $userId): Collection
    {
        return UserIdentity::where('user_id', $userId)
            ->whereNull('revoked_at')
            ->get();
    }

    /**
     * 유저의 모든 토큰 삭제
     *
     * @param User $user
     * @return void
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * 유저 생성
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }
}