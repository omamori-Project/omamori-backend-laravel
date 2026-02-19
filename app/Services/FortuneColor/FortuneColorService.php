<?php

declare(strict_types=1);

namespace App\Services\FortuneColor;

use App\Models\FortuneColor;
use App\Repositories\FortuneColor\FortuneColorRepository;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class FortuneColorService extends BaseService
{
    public function __construct(
        private readonly FortuneColorRepository $fortuneColorRepository,
    ) {
    }

    /**
     * 행운컬러 목록 조회 (Public)
     *
     * @param int $page
     * @param int $size
     * @param bool|null $isActive
     * @return LengthAwarePaginator
     */
    public function paginate(int $page = 1, int $size = 20, ?bool $isActive = true): LengthAwarePaginator
    {
        return $this->fortuneColorRepository->paginatePublic($page, $size, $isActive);
    }

    /**
     * 행운컬러 단건 조회 (Public)
     *
     * @param int $fortuneColorId
     * @return FortuneColor
     */
    public function show(int $fortuneColorId): FortuneColor
    {
        /** @var FortuneColor $fortuneColor */
        $fortuneColor = $this->fortuneColorRepository->findOrFail($fortuneColorId);

        return $fortuneColor;
    }

    /**
     * 오늘의 행운컬러 추천 (Public)
     *
     * @param string $birthday
     * @return FortuneColor
     * @throws ValidationException
     */
    public function recommendToday(string $birthday): FortuneColor
    {
        $date = $this->parseBirthday($birthday);

        $colors = $this->fortuneColorRepository->getActiveAll();

        if ($colors->isEmpty()) {
            throw ValidationException::withMessages([
                'fortuneColors' => ['활성화된 행운 컬러가 없습니다.'],
            ]);
        }

        // deterministic seed: YYYYMMDD
        $seed = (int) $date->format('Ymd');
        $index = $seed % $colors->count();

        /** @var FortuneColor $selected */
        $selected = $colors->values()->get($index);

        return $selected;
    }

    /**
     * 생년월일 문자열 파싱
     *
     * @param string $birthday
     * @return Carbon
     * @throws ValidationException
     */
    private function parseBirthday(string $birthday): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $birthday)->startOfDay();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'birthday' => ['birthday 형식이 올바르지 않습니다. (YYYY-MM-DD)'],
            ]);
        }
    }
}