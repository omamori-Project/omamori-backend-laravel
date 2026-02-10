<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserIdentity;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Auth\SocialAuthRepository;
use App\Services\BaseService;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class SocialAuthService extends BaseService
{
    public function __construct(
        private AuthRepository $authRepository,
        private SocialAuthRepository $socialAuthRepository
    ) {}

    /**
     * OAuth 리다이렉트 URL 반환
     *
     * @param string $provider
     * @return string
     */
    public function getRedirectUrl(string $provider): string
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver->stateless()->redirect()->getTargetUrl();
    }

    /**
     * OAuth 콜백 처리
     * - 기존 Identity → 로그인
     * - 기존 이메일 유저 → Identity 연결 후 로그인
     * - 신규 유저 → 유저 + Identity 생성 후 로그인
     *
     * @param string $provider
     * @return array [user, token]
     */
    public function handleCallback(string $provider): array
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);
        $socialUser = $driver->stateless()->user();

        return $this->transaction(function () use ($provider, $socialUser) {
            $identity = $this->socialAuthRepository->findByProvider(
                $provider,
                $socialUser->getId()
            );

            if ($identity) {
                $user = $identity->user;
                $this->authRepository->updateLastLogin($user);
                $this->authRepository->updateLastUsed($identity);
            } else {
                $user = $this->authRepository->findByEmail($socialUser->getEmail())
                    ?? $this->authRepository->create([
                        'email' => $socialUser->getEmail(),
                        'name' => $socialUser->getName(),
                    ]);

                $this->socialAuthRepository->createIdentity($user, [
                    'provider' => $provider,
                    'provider_user_id' => $socialUser->getId(),
                    'email' => $socialUser->getEmail(),
                    'profile' => [
                        'name' => $socialUser->getName(),
                        'avatar' => $socialUser->getAvatar(),
                    ],
                ]);

                $this->authRepository->updateLastLogin($user);
            }

            $token = $user->createToken('auth')->plainTextToken;

            return ['user' => $user, 'token' => $token];
        });
    }

    /**
     * 기존 계정에 소셜 계정 연결
     * - 프론트에서 받은 access_token으로 소셜 유저 정보 조회
     * - 이미 다른 계정에 연결된 경우 예외 발생
     *
     * @param User $user
     * @param string $provider
     * @param string $accessToken
     * @return UserIdentity
     * @throws \Exception
     */
    public function link(User $user, string $provider, string $accessToken): UserIdentity
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);
        $socialUser = $driver->stateless()->userFromToken($accessToken);

        $existing = $this->socialAuthRepository->findByProvider(
            $provider,
            $socialUser->getId()
        );

        if ($existing) {
            throw new \Exception('이 소셜 계정은 이미 다른 계정에 연결되어 있습니다.');
        }

        $myIdentity = $this->socialAuthRepository->findUserIdentity(
            $user->id,
            $provider
        );

        if ($myIdentity) {
            throw new \Exception('이미 연결된 소셜 계정입니다.');
        }

        return $this->socialAuthRepository->createIdentity($user, [
            'provider' => $provider,
            'provider_user_id' => $socialUser->getId(),
            'email' => $socialUser->getEmail(),
            'profile' => [
                'name' => $socialUser->getName(),
                'avatar' => $socialUser->getAvatar(),
            ],
        ]);
    }

    /**
     * 소셜 계정 연결 해제
     * - 최소 1개 로그인 수단은 남아있어야 함
     *
     * @param User $user
     * @param string $provider
     * @return void
     * @throws \Exception
     */
    public function disconnect(User $user, string $provider): void
    {
        $identity = $this->socialAuthRepository->findUserIdentity(
            $user->id,
            $provider
        );

        if (!$identity) {
            throw new \Exception('연결된 계정을 찾을 수 없습니다.');
        }

        $activeCount = $this->socialAuthRepository->countActiveIdentities($user->id);

        if ($activeCount <= 1) {
            throw new \Exception('최소 1개의 로그인 수단이 필요합니다.');
        }

        $this->socialAuthRepository->revokeIdentity($identity);
    }
}