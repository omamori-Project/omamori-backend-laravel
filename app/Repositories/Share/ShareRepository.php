<?php

namespace App\Repositories\Share;

use App\Models\Share;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ShareRepository
 *
 * 오마모리 공유(Share) 엔티티에 대한 DB 접근 레이어
 * - 공통 CRUD(find/create/update/delete)는 BaseRepository를 사용
 * - Share 도메인에 특화된 조회 쿼리만 이 레포지토리에 정의
 */
class ShareRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new Share();
    }

    /**
     * 특정 오마모리에 대해 "현재 유효한" 공유 1건 조회
     * - soft delete 제외
     * - is_active = true
     * - expires_at이 null이거나, 현재 시각보다 미래
     * @param int $omamoriId
     * @return Share|null
     */
    public function findActiveByOmamoriId(int $omamoriId): ?Share
    {
        /** @var Share|null $share */
        $share = Share::query()
            ->where('omamori_id', $omamoriId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('id')
            ->first();

        return $share;
    }

    /**
     * 특정 오마모리의 공유 목록 조회
     * - soft delete 제외
     * - 최신순
     *
     * @param int $omamoriId
     * @return Collection<int, Share>
     */
    public function listByOmamoriId(int $omamoriId): Collection
    {
        /** @var Collection<int, Share> $shares */
        $shares = Share::query()
            ->where('omamori_id', $omamoriId)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->get();

        return $shares;
    }

    /**
     * token 기준 단건 조회
     * - soft delete 제외
     *
     * @param string $token
     * @return Share|null
     */
    public function findByToken(string $token): ?Share
    {
        /** @var Share|null $share */
        $share = Share::query()
            ->where('token', $token)
            ->whereNull('deleted_at')
            ->first();

        return $share;
    }

    /**
     * 공유 조회수 증가
     *
     * @param Share $share
     * @return void
     */
    public function incrementViewCount(Share $share): void
    {
        $share->increment('view_count');
    }
}