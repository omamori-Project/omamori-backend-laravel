<?php

declare(strict_types=1);

namespace App\Repositories\Frame;

use App\Models\Frame;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Frame Repository
 */
class FrameRepository extends BaseRepository
{
    /**
     * Repository 대상 모델 반환
     *
     * @return Model
     */
    protected function getModel(): Model
    {
        return new Frame();
    }

    /**
     * (Public/User) 프레임 목록 조회(페이지네이션)
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
     * (Admin) 프레임 목록 조회(페이지네이션)
     *
     * @param int $page
     * @param int $size
     * @param bool|null $isActive
     * @param bool $withTrashed
     * @param bool $onlyTrashed
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
     * (Public/User) 활성 프레임 전체 조회(선택 UI 등에 사용)
     *
     * @return Collection<int, Frame>
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
     * @return Frame
     */
    public function findWithTrashedOrFail(int $id): Frame
    {
        /** @var Frame $frame */
        $frame = $this->getModel()
            ->newQuery()
            ->withTrashed()
            ->whereKey($id)
            ->firstOrFail();

        return $frame;
    }

    /**
     * 복구(restore)
     *
     * @param Frame $frame
     * @return Frame
     */
    public function restore(Frame $frame): Frame
    {
        $frame->restore();

        return $frame->refresh();
    }

    /**
     * 영구 삭제(force delete)
     *
     * @param Frame $frame
     * @return void
     */
    public function forceDelete(Frame $frame): void
    {
        $frame->forceDelete();
    }
}