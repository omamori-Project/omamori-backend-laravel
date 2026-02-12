<?php

namespace App\Services\Omamori;

use App\Models\Omamori;
use App\Models\OmamoriElement;
use App\Repositories\Omamori\OmamoriDuplicateRepository;
use Illuminate\Support\Facades\DB;

/**
 * Class OmamoriDuplicateService
 *
 * 오마모리 복제 유스케이스 처리
 *
 * 정책:
 * - 본인 소유 오마모리만 복제 가능
 * - 복제본은 status='draft', published_at=null
 * - omamori_elements는 전부 복제 (background 포함)
 */
class OmamoriDuplicateService
{
    public function __construct(
        private readonly OmamoriDuplicateRepository $repository
    ) {}

    /**
     * 오마모리 및 요소를 복제하여 새 오마모리를 생성
     *
     * @param int $userId 로그인 유저 ID
     * @param int $omamoriId 원본 오마모리 ID
     * @return Omamori 복제된 오마모리
     *
     * @throws \Throwable 트랜잭션 내 예외 발생 시 롤백
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403/404 등
     */
    public function duplicate(int $userId, int $omamoriId): Omamori
    {
        $original = $this->repository->findOmamoriOrFail($omamoriId);

        // 권한 정책: 소유자만 복제 가능
        if ((int) $original->user_id !== (int) $userId) {
            abort(403, 'Forbidden');
        }

        return DB::transaction(function () use ($original, $userId) {

            /** @var Omamori $copy */
            $copy = $original->replicate();

            // 복제 정책 적용
            $copy->user_id = $userId;
            $copy->status = 'draft';
            $copy->published_at = null;

            $copy->save();

            // 요소 복제
            $elements = $this->repository->getElementsByOmamoriId($original->id);

            /** @var OmamoriElement $element */
            foreach ($elements as $element) {
                $newElement = $element->replicate();
                $newElement->omamori_id = $copy->id;
                $newElement->save();
            }

            return $copy->fresh();
        });
    }
}