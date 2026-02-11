<?php

namespace App\Services\Omamori;

use App\Models\Omamori;
use App\Repositories\Omamori\OmamoriRepository;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}