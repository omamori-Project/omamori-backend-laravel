<?php

declare(strict_types=1);

namespace App\Repositories\FortuneColor;

use App\Models\FortuneColor;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * FortuneColor Repository
 */
class FortuneColorRepository extends BaseRepository
{
    /**
     * Repository 대상 모델 반환
     *
     * @return Model
     */
    protected function getModel(): Model
    {
        return new FortuneColor();
    }

    /**
     * (Public/User) 행운컬러 목록 조회(페이지네이션)
     * - SoftDeletes 기본 스코프로 trashed는 자동 제외
     *
     * @param int $page
     * @param int $size
     * @param bool|null $isActive true면 활성만, false면 비활성만, null이면 전체
     * @return LengthAwarePaginator
     */
    public function paginatePublic(int $page = 1, int $size = 20, ?bool $isActive = true): LengthAwarePaginator
    {
        $query = $this->getModel()
            ->newQuery()
            ->orderBy('id');

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->paginate(
            $size,
            ['*'],
            'page',
            $page
        );
    }

    /**
     * (Admin) 행운컬러 목록 조회(페이지네이션)
     *
     * @param int $page
     * @param int $size
     * @param bool|null $isActive true/false/null
     * @param bool $withTrashed 소프트 삭제 포함 여부
     * @param bool $onlyTrashed 소프트 삭제만 조회 여부
     * @return LengthAwarePaginator
     */
    public function paginateAdmin(
        int $page = 1,
        int $size = 20,
        ?bool $isActive = null,
        bool $withTrashed = false,
        bool $onlyTrashed = false,
    ): LengthAwarePaginator {
        $query = $this->getModel()
            ->newQuery()
            ->orderBy('id');

        if ($onlyTrashed) {
            $query->onlyTrashed();
        } elseif ($withTrashed) {
            $query->withTrashed();
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->paginate(
            $size,
            ['*'],
            'page',
            $page
        );
    }

    /**
     * (Public/User) 활성 행운컬러 전체 조회(추천/랜덤 선택 등에 사용)
     *
     * @return Collection<int, FortuneColor>
     */
    public function getActiveAll(): Collection
    {
        return $this->getModel()
            ->newQuery()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    /**
     * 단건 조회(없으면 404) - trashed 포함(Admin용)
     *
     * @param int $id
     * @return FortuneColor
     */
    public function findWithTrashedOrFail(int $id): FortuneColor
    {
        /** @var FortuneColor $fortuneColor */
        $fortuneColor = $this->getModel()
            ->newQuery()
            ->withTrashed()
            ->whereKey($id)
            ->firstOrFail();

        return $fortuneColor;
    }

    /**
     * 복구(restore)
     *
     * @param FortuneColor $fortuneColor
     * @return FortuneColor
     */
    public function restore(FortuneColor $fortuneColor): FortuneColor
    {
        $fortuneColor->restore();

        return $fortuneColor->refresh();
    }

    /**
     * 영구 삭제(force delete)
     *
     * @param FortuneColor $fortuneColor
     * @return void
     */
    public function forceDelete(FortuneColor $fortuneColor): void
    {
        $fortuneColor->forceDelete();
    }
}