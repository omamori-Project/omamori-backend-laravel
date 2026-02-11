<?php

namespace App\Repositories\Omamori;

use App\Models\Omamori;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class OmamoriRepository extends BaseRepository
{
    /**
     * @return Model
     */
    protected function getModel(): Model
    {
        return new Omamori();
    }

    /**
     * 내 오마모리 목록 조회 (필터 + 정렬 + 페이지네이션)
     *
     * @param int $userId
     * @param array $filters ['status', 'sort', 'size']
     * @return LengthAwarePaginator
     */
    public function getByUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        $query = Omamori::where('user_id', $userId)
            ->with(['fortuneColor', 'frame']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'oldest' => $query->oldest(),
            'title'  => $query->orderBy('title'),
            default  => $query->latest(),
        };

        $size = $filters['size'] ?? 10;

        return $query->paginate($size);
    }

    /**
     * 오마모리 단건 조회 (관계 데이터 포함)
     *
     * @param int $id
     * @return Omamori
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findWithRelations(int $id): Omamori
    {
        return Omamori::with(['fortuneColor', 'frame', 'elements'])
            ->findOrFail($id);
    }

    /**
     * 오마모리 생성 (초기 상태: draft)
     *
     * @param int $userId
     * @param array $data
     * @return Omamori
     */
    public function createOmamori(int $userId, array $data): Omamori
    {
        /** @var Omamori $omamori */
        $omamori = $this->create([
            ...$data,
            'user_id' => $userId,
            'status'  => 'draft',
        ]);

        return $omamori->load(['fortuneColor', 'frame']);
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
        $this->update($omamori, $data);

        return $omamori->load(['fortuneColor', 'frame']);
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
        $this->update($omamori, ['back_message' => $message]);

        return $omamori;
    }
}