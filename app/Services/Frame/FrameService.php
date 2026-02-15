<?php

declare(strict_types=1);

namespace App\Services\Frame;

use App\Repositories\Frame\FrameRepository;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FrameService extends BaseService
{
    public function __construct(
        private readonly FrameRepository $frameRepository,
    ) {
    }

    /**
     * 프레임 목록 조회 (Public)
     *
     * @param int $page
     * @param int $size
     * @param bool|null $isActive
     * @return LengthAwarePaginator
     */
    public function paginate(int $page = 1, int $size = 20, ?bool $isActive = true): LengthAwarePaginator
    {
        return $this->frameRepository->paginatePublic($page, $size, $isActive);
    }
}