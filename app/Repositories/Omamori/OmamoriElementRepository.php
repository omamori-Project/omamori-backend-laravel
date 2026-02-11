<?php

namespace App\Repositories\Omamori;

use App\Models\OmamoriElement;
use Illuminate\Database\Eloquent\Collection;

class OmamoriElementRepository
{
    /**
     * 오마모리 요소 생성
     *
     * @param array $data
     * @return OmamoriElement
     */
    public function create(array $data): OmamoriElement
    {
        return OmamoriElement::create($data);
    }

    /**
     * 오마모리 요소 수정
     *
     * @param OmamoriElement $element
     * @param array $data
     * @return OmamoriElement
     */
    public function update(OmamoriElement $element, array $data): OmamoriElement
    {
        $element->fill($data);
        $element->save();

        return $element->refresh();
    }

    /**
     * 오마모리 요소 삭제 (Soft Delete)
     *
     * @param OmamoriElement $element
     * @return void
     */
    public function delete(OmamoriElement $element): void
    {
        $element->delete();
    }

    /**
     * ID로 요소 조회
     *
     * @param int $id
     * @return OmamoriElement|null
     */
    public function findById(int $id): ?OmamoriElement
    {
        return OmamoriElement::query()->find($id);
    }

    /**
     * 특정 오마모리의 background 요소 조회
     *
     * @param int $omamoriId
     * @return OmamoriElement|null
     */
    public function findBackgroundByOmamoriId(int $omamoriId): ?OmamoriElement
    {
        return OmamoriElement::query()
            ->where('omamori_id', $omamoriId)
            ->where('type', 'background')
            ->first();
    }

    /**
     * 특정 오마모리의 non-background 요소 목록 조회 (layer 순 정렬)
     *
     * @param int $omamoriId
     * @return Collection<int, OmamoriElement>
     */
    public function listNonBackgroundByOmamoriId(int $omamoriId): Collection
    {
        return OmamoriElement::query()
            ->where('omamori_id', $omamoriId)
            ->whereIn('type', ['text', 'stamp'])
            ->orderBy('layer')
            ->get();
    }

    /**
     * non-background 요소 중 최대 layer 값 조회
     *
     * @param int $omamoriId
     * @return int|null
     */
    public function getMaxLayerOfNonBackground(int $omamoriId): ?int
    {
        return OmamoriElement::query()
            ->where('omamori_id', $omamoriId)
            ->whereIn('type', ['text', 'stamp'])
            ->max('layer');
    }

    /**
     * background 요소 upsert (존재하면 수정, 없으면 생성)
     * layer는 항상 0으로 강제
     *
     * @param int $omamoriId
     * @param array $data
     * @return OmamoriElement
     */
    public function upsertBackground(int $omamoriId, array $data): OmamoriElement
    {
        $existing = $this->findBackgroundByOmamoriId($omamoriId);

        $payload = array_merge($data, [
            'omamori_id' => $omamoriId,
            'type' => 'background',
            'layer' => 0,
        ]);

        if ($existing) {
            return $this->update($existing, $payload);
        }

        return $this->create($payload);
    }

    /**
     * 요소들의 layer 업데이트
     *
     * @param int $omamoriId
     * @param array<int,int> $idToLayerMap [element_id => layer]
     * @return void
     */
    public function bulkUpdateLayer(int $omamoriId, array $idToLayerMap): void
    {
        foreach ($idToLayerMap as $id => $layer) {
            OmamoriElement::query()
                ->where('omamori_id', $omamoriId)
                ->where('id', $id)
                ->update(['layer' => $layer]);
        }
    }
}