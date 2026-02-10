<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Auth\AuthRepository;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService extends BaseService
{
    public function __construct(
        private AuthRepository $authRepository
    ) {}

    /**
     * 회원가입 처리
     * - 유저 생성 + Local Identity 생성 + 토큰 발급
     *
     * @param array $data [email, password, name]
     * @return array [user, token]
     */
    public function register(array $data): array
    {
        return $this->transaction(function () use ($data) {
            $user = $this->authRepository->create([
                'email' => $data['email'],
                'name' => $data['name'],
            ]);

            $this->authRepository->createIdentity($user, [
                'provider' => 'local',
                'provider_user_id' => $user->id,
                'email' => $data['email'],
                'password_hash' => Hash::make($data['password']),
            ]);

            $token = $user->createToken('auth')->plainTextToken;

            return ['user' => $user, 'token' => $token];
        });
    }

    /**
     * 로그인 처리
     * - 이메일/비밀번호 검증 → 토큰 발급
     *
     * @param array $data [email, password]
     * @return array [user, token]
     * @throws ValidationException
     */
    public function login(array $data): array
    {
        $user = $this->authRepository->findByEmail($data['email']);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['이메일 또는 비밀번호가 일치하지 않습니다.'],
            ]);
        }

        $identity = $this->authRepository->findLocalIdentity($user->id);

        if (!$identity || !Hash::check($data['password'], $identity->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['이메일 또는 비밀번호가 일치하지 않습니다.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['비활성화된 계정입니다.'],
            ]);
        }

        $this->authRepository->updateLastLogin($user);
        $this->authRepository->updateLastUsed($identity);

        $token = $user->createToken('auth')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * 로그아웃 처리
     * - 현재 토큰 삭제
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        /** @var PersonalAccessToken $token */
        $token = $user->currentAccessToken();
        $token->delete();
    }

    /**
     * 회원 정보 수정
     *
     * @param User $user
     * @param array $data [name?, password?]
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        if (isset($data['name'])) {
            $this->authRepository->update($user, ['name' => $data['name']]);
        }

        if (isset($data['password'])) {
            $identity = $this->authRepository->findLocalIdentity($user->id);

            if ($identity) {
                $identity->update([
                    'password_hash' => Hash::make($data['password']),
                ]);
            }
        }

        return $user->refresh();
    }

    /**
     * 회원 탈퇴 처리
     * - 모든 토큰 삭제 + 소프트 삭제
     *
     * @param User $user
     * @return void
     */
    public function deleteAccount(User $user): void
    {
        $this->transaction(function () use ($user) {
            $this->authRepository->revokeAllTokens($user);
            $this->authRepository->delete($user);
        });
    }

    /**
     * 내 로그인 수단 목록 조회
     *
     * @param User $user
     * @return Collection
     */
    public function getIdentities(User $user): Collection
    {
        return $this->authRepository->getActiveIdentities($user->id);
    }
}