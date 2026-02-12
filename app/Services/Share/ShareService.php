<?php

namespace App\Services\Share;

use App\Models\Omamori;
use App\Models\Share;
use App\Models\User;
use App\Services\BaseService;
use App\Repositories\Share\ShareRepository;
use App\Exceptions\BusinessException;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ShareService
 *
 * 오마모리 공유(Share) 도메인의 비즈니스 로직을 처리한다.
 * - 권한/상태 검증(published, 만료/비활성 등)
 * - 토큰 발급/재발급(rotate)
 * - 공개 조회 조건 검증 + 조회수 증가
 */
class ShareService extends BaseService
{
    public function __construct(
        private readonly ShareRepository $shareRepository,
    ) {
    }

    /**
     * 공유 링크 생성 또는 기존 활성 공유 반환 
     *
     * 규칙:
     * - 오마모리는 published 상태여야 함
     * - 이미 활성 + 미만료 share가 있으면 그 share를 반환
     * - 없으면 새로 생성(token 발급)
     *
     * @param Omamori $omamori
     * @param User $user
     * @return Share
     *
     * @throws BusinessException
     */
    public function createOrGetActiveShare(Omamori $omamori, User $user): Share
    {
        if ($omamori->status !== 'published') {
            throw new BusinessException('published 상태에서만 공유 링크를 생성할 수 있습니다.', 409);
        }

        return $this->transaction(function () use ($omamori, $user) {
            $existing = $this->shareRepository->findActiveByOmamoriId($omamori->id);
            if ($existing) {
                return $existing;
            }

            /** @var Share $created */
            $created = $this->shareRepository->create([
                'omamori_id' => $omamori->id,
                'user_id'    => $user->id,
                'token'      => (string) Str::uuid(),
                'is_active'  => true,
                'expires_at' => null,
                'view_count' => 0,
            ]);

            return $created;
        });
    }

    /**
     * 특정 오마모리의 공유 링크 목록
     *
     * @return Collection<int, Share>
     * @throws BusinessException
     */
    public function listByOmamori(Omamori $omamori, User $user): Collection
    {
        if ((int) $omamori->user_id !== (int) $user->id) {
            throw new BusinessException('본인 오마모리만 조회할 수 있습니다.', 403);
        }

        return $this->shareRepository->listByOmamoriId($omamori->id);
    }

    /**
     * 공유 설정 변경
     *
     * 허용 변경:
     * - is_active (bool)
     * - expires_at (nullable datetime)
     * - rotate_token (bool) -> true면 token 재발급
     *
    * @param array<string, mixed> $payload
    * @throws BusinessException
    */
    public function updateShare(Share $share, array $payload): Share
    {
        return $this->transaction(function () use ($share, $payload) {
            $data = [];

            if (array_key_exists('is_active', $payload)) {
                $data['is_active'] = (bool) $payload['is_active'];
            }

            if (array_key_exists('expires_at', $payload)) {
                $expiresAt = $payload['expires_at'];

                if ($expiresAt === null) {
                    $data['expires_at'] = null;
                } else {
                    $data['expires_at'] = $expiresAt instanceof Carbon
                        ? $expiresAt
                        : Carbon::parse((string) $expiresAt);
                }
            }

            if (!empty($payload['rotate_token'])) {
                $data['token'] = (string) Str::uuid();
            }

            if (!empty($data)) {
                $this->shareRepository->update($share, $data); // BaseRepository::update returns bool
            }

            /** @var Share $fresh */
            $fresh = $this->shareRepository->findOrFail($share->id);
            return $fresh;
        });
    }

    /**
     * 공유 링크 삭제(soft delete)
     *
     * @param Share $share
     * @return void
     */
    public function deleteShare(Share $share): void
    {
        $this->transaction(function () use ($share) {
            $this->shareRepository->delete($share);
        });
    }

    /**
     * 공개 링크 토큰으로 오마모리 공유 조회(비로그인)
     *
     * 규칙:
     * - share 존재 + soft delete 아님
     * - is_active = true
     * - expires_at이 있으면 만료 전
     * - 연결된 omamori가 published
     * - 조회 성공 시 view_count 증가
     *
     * @param string $token
     * @return Share
     *
     * @throws BusinessException
     */
    public function resolvePublic(string $token): Share
    {
        return $this->transaction(function () use ($token) {
    
            // 유효한 share 조회 
            $share = $this->shareRepository->findActiveByToken($token);
    
            if (!$share) {
                throw new BusinessException('공유 링크를 찾을 수 없습니다.', 404);
            }
    
            // 연관 오마모리 상태 확인
            $share->loadMissing(['omamori']);
    
            if (!$share->omamori || $share->omamori->status !== 'published') {
                throw new BusinessException('공유 링크를 찾을 수 없습니다.', 404);
            }
    
            // 조회수 증가 
            $this->shareRepository->incrementViewCount($share);
    
            /** @var Share $fresh */
            $fresh = $this->shareRepository->findOrFail($share->id);
    
            return $fresh;
        });
    }
    
}