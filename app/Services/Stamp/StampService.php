<?php

namespace App\Services\Stamp;

use App\Repositories\Stamp\StampRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * 스탬프 카탈로그 서비스.
 *
 * 비즈니스 로직은 최소화하고 Repository에 위임한다.
 */
class StampService
{
    /**
     * @param  StampRepository  $stampCatalogRepository
     */
    public function __construct(
        private readonly StampRepository $stampCatalogRepository
    ) {}

    /**
     * 스탬프 목록 페이징 조회.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->stampCatalogRepository->paginate($filters);
    }
}