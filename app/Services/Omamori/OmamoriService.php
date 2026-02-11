<?php

namespace App\Services\Omamori;

use App\Models\Omamori;
use App\Repositories\Omamori\OmamoriRepository;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OmamoriService extends BaseService
{
    /**
     * @param OmamoriRepository $omamoriRepository
     */
    public function __construct(
        private OmamoriRepository $omamoriRepository
    ) {}

    /**
     * 내 오마모리 목록 조회
     *
     * @param int $userId
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getMyOmamoris(int $userId, array $filters): LengthAwarePaginator
    {
        return $this->omamoriRepository->getByUser($userId, $filters);
    }

    /**
     * 오마모리 상세 조회
     *
     * @param int $id
     * @return Omamori
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getOmamori(int $id): Omamori
    {
        return $this->omamoriRepository->findWithRelations($id);
    }

    /**
     * 오마모리 생성
     *
     * @param int $userId
     * @param array $data
     * @return Omamori
     */
    public function createOmamori(int $userId, array $data): Omamori
    {
        return $this->omamoriRepository->createOmamori($userId, $data);
    }

    /**
     * 오마모리 제작 정보 수정
     *
     * @param Omamori $omamori
     * @param array $data
     * @return Omamori
     */
    public function updateOmamori(Omamori $omamori, array $data): Omamori
    {
        return $this->omamoriRepository->updateOmamori($omamori, $data);
    }

    /**
     * 뒷면 메시지 수정
     *
     * @param Omamori $omamori
     * @param string $message
     * @return Omamori
     */
    public function updateBackMessage(Omamori $omamori, string $message): Omamori
    {
        return $this->omamoriRepository->updateBackMessage($omamori, $message);
    }

    /**
     * 오마모리 삭제
     *
     * @param Omamori $omamori
     * @return void
     */
    public function deleteOmamori(Omamori $omamori): void
    {
        $this->omamoriRepository->delete($omamori);
    }
    /**
     * 오마모리를 draft 상태로 저장
     *
     * 규칙:
     * - published → draft 되돌리기 금지
     * - 이미 draft면 그대로 반환 (idempotent)
     *
     * @param  Omamori  $omamori
     * @return Omamori
     *
     * @throws HttpException (409)
     */
    public function saveDraft(Omamori $omamori): Omamori
    {
        if ($omamori->status === Omamori::STATUS_PUBLISHED) {
            throw new HttpException(
                409,
                '이미 발행된 오마모리는 임시저장(draft)으로 되돌릴 수 없습니다.'
            );
        }

        if ($omamori->status === Omamori::STATUS_DRAFT) {
            return $omamori->fresh();
        }

        return $this->omamoriRepository->setStatusDraft($omamori);
    }

    /**
     * 오마모리를 published 상태로 전환
     *
     * 규칙:
     * - draft → published만 허용
     * - 이미 published면 그대로 반환 (idempotent)
     * - 발행 조건을 만족해야 함
     *
     * @param  Omamori  $omamori
     * @return Omamori
     *
     * @throws ValidationException
     */
    public function publish(Omamori $omamori): Omamori
    {
        if ($omamori->status === Omamori::STATUS_PUBLISHED) {
            return $omamori->fresh();
        }

        $this->assertPublishable($omamori);

        return $this->omamoriRepository->setStatusPublished($omamori);
    }

    /**
     * 오마모리가 발행 가능한 상태인지 검증
     *
     * 검증 항목:
     * - 포춘 컬러 선택 여부
     * - 프레임 선택 여부
     * - background 제외 요소 최소 1개 존재
     *
     * @param  Omamori  $omamori
     * @return void
     *
     * @throws ValidationException
     */
    private function assertPublishable(Omamori $omamori): void
    {
        $errors = [];

        if (empty($omamori->applied_fortune_color_id)) {
            $errors['applied_fortune_color_id'][] = '포춘 컬러를 선택해야 발행할 수 있습니다.';
        }

        if (empty($omamori->applied_frame_id)) {
            $errors['applied_frame_id'][] = '프레임을 선택해야 발행할 수 있습니다.';
        }

        $nonBgCount = $this->omamoriRepository
            ->countNonBackgroundElements($omamori->id);

        if ($nonBgCount < 1) {
            $errors['elements'][] = '텍스트 또는 스탬프 요소가 최소 1개 이상 필요합니다.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}