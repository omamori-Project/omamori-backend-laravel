<?php

namespace App\Repositories\Omamori;

use App\Models\Omamori;
use App\Models\OmamoriElement;
use Illuminate\Database\Eloquent\Collection;

/**
 * 오마모리 복제에 필요한 조회를 담당하는 레이어
 */
class OmamoriDuplicateRepository
{
    /**
     * 오마모리를 ID로 조회
     * - 존재하지 않으면 404 예외 발생
     *
     * @param int $omamoriId
     * @return Omamori
     */
    public function findOmamoriOrFail(int $omamoriId): Omamori
    {
        return Omamori::query()->findOrFail($omamoriId);
    }

    /**
     * 특정 오마모리의 요소들을 레이어 순으로 조회
     *
     * @param int $omamoriId
     * @return Collection<int, OmamoriElement>
     */
    public function getElementsByOmamoriId(int $omamoriId): Collection
    {
        return OmamoriElement::query()
            ->where('omamori_id', $omamoriId)
            ->orderBy('layer')
            ->get();
    }
}