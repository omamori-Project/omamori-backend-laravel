<?php

namespace App\Services\Omamori;

use App\Models\Omamori;
use App\Models\OmamoriElement;
use App\Repositories\Omamori\OmamoriElementRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OmamoriElementService
{
    /**
     * @var OmamoriElementRepository
     */
    protected OmamoriElementRepository $repo;

    /**
     * @param OmamoriElementRepository $repo
     */
    public function __construct(OmamoriElementRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * 오마모리 요소 생성
     * - background면 upsert + layer=0 강제
     * - text/stamp면 다음 layer 계산 후 생성
     *
     * @param Omamori $omamori
     * @param array $data
     * @return OmamoriElement
     */
    public function store(Omamori $omamori, array $data): OmamoriElement
    {
        $type = $data['type'];

        if ($type === 'background') {
            return $this->storeBackground($omamori, $data);
        }

        if (!in_array($type, ['text', 'stamp'], true)) {
            throw ValidationException::withMessages([
                'type' => ['Invalid type.'],
            ]);
        }

        return DB::transaction(function () use ($omamori, $data, $type) {
            $max = $this->repo->getMaxLayerOfNonBackground($omamori->id);
            $nextLayer = is_null($max) ? 1 : ($max + 1);

            return $this->repo->create([
                'omamori_id' => $omamori->id,
                'type' => $type,
                'layer' => $nextLayer,
                'props' => $data['props'] ?? [],
                'transform' => $data['transform'] ?? [],
            ]);
        });
    }

    /**
     * 오마모리 요소 수정
     * - type/layer 변경은 금지
     *
     * @param Omamori $omamori
     * @param OmamoriElement $element
     * @param array $data
     * @return OmamoriElement
     */
    public function update(Omamori $omamori, OmamoriElement $element, array $data): OmamoriElement
    {
      // 소속 오마모리 체크
        if ($element->omamori_id !== $omamori->id) {
            throw ValidationException::withMessages([
                'element' => ['Element does not belong to the omamori.'],
            ]);
        }

        $payload = [];

        if (array_key_exists('props', $data)) {
            $payload['props'] = $data['props'] ?? [];
        }
        if (array_key_exists('transform', $data)) {
            $payload['transform'] = $data['transform'] ?? [];
        }

        return $this->repo->update($element, $payload);
    }

    /**
     * 오마모리 요소 삭제
     *
     * @param Omamori $omamori
     * @param OmamoriElement $element
     * @return void
     */
    public function destroy(Omamori $omamori, OmamoriElement $element): void
    {
        if ($element->omamori_id !== $omamori->id) {
            throw ValidationException::withMessages([
                'element' => ['Element does not belong to the omamori.'],
            ]);
        }

        $this->repo->delete($element);
    }

    /**
     * 요소 레이어 재정렬
     * 규칙:
     * - background는 reorder 대상이 아님 (포함되면 422)
     * - non-background 전체 요소 ID 목록과 정확히 일치해야 함 (누락/추가 모두 422)
     * - layer는 1..n으로 재할당 (background는 항상 0)
     *
     * @param Omamori $omamori
     * @param array $elementIds
     * @return void
     */
    public function reorder(Omamori $omamori, array $elementIds): void
    {
        DB::transaction(function () use ($omamori, $elementIds) {

            // 현재 non-background 전체 목록
            $current = $this->repo->listNonBackgroundByOmamoriId($omamori->id);

            $currentIds = $current->pluck('id')->map(fn ($v) => (int) $v)->all();
            $requestedIds = array_map('intval', $elementIds);

            // 1) background가 섞였는지 체크 
            // 2) 전체 일치 체크 
            sort($currentIds);
            $reqSorted = $requestedIds;
            sort($reqSorted);

            if ($currentIds !== $reqSorted) {
                throw ValidationException::withMessages([
                    'elementIds' => ['elementIds must match all non-background element IDs exactly (excluding background).'],
                ]);
            }

            // 재할당
            $idToLayerMap = [];
            $layer = 1;
            foreach ($requestedIds as $id) {
                $idToLayerMap[$id] = $layer;
                $layer++;
            }

            $this->repo->bulkUpdateLayer($omamori->id, $idToLayerMap);
        });
    }

    /**
     * background 요소 생성/수정
     * layer=0 강제
     *
     * @param Omamori $omamori
     * @param array $data
     * @return OmamoriElement
     */
    protected function storeBackground(Omamori $omamori, array $data): OmamoriElement
    {
        return DB::transaction(function () use ($omamori, $data) {
            return $this->repo->upsertBackground($omamori->id, [
                'props' => $data['props'] ?? [],
                'transform' => $data['transform'] ?? [],
            ]);
        });
    }
}