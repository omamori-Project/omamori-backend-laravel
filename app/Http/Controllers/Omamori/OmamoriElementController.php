<?php

namespace App\Http\Controllers\Omamori;

use App\Http\Controllers\Controller;
use App\Http\Requests\Omamori\ReorderElementsRequest;
use App\Http\Requests\Omamori\StoreElementRequest;
use App\Http\Requests\Omamori\UpdateElementRequest;
use App\Http\Resources\Omamori\OmamoriElementResource;
use App\Models\Omamori;
use App\Models\OmamoriElement;
use App\Services\Omamori\OmamoriElementService;
use Illuminate\Validation\ValidationException;

class OmamoriElementController extends Controller
{
    /**
     * @var OmamoriElementService
     */
    protected OmamoriElementService $service;

    /**
     * @param OmamoriElementService $service
     */
    public function __construct(OmamoriElementService $service)
    {
        $this->service = $service;
    }

    /**
     * 요소 생성
     *
     * @param StoreElementRequest $request
     * @param Omamori $omamori
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreElementRequest $request, Omamori $omamori)
    {
        $this->authorize('update', $omamori);

        $element = $this->service->store($omamori, $request->validated());

        return $this->created(new OmamoriElementResource($element));
    }

    /**
     * 요소 수정
     *
     * @param UpdateElementRequest $request
     * @param Omamori $omamori
     * @param OmamoriElement $element
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateElementRequest $request, Omamori $omamori, OmamoriElement $element)
    {
        $this->authorize('update', $omamori);

        $updated = $this->service->update($omamori, $element, $request->validated());

        return $this->success(new OmamoriElementResource($updated));
    }

    /**
     * 요소 삭제
     *
     * @param Omamori $omamori
     * @param OmamoriElement $element
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Omamori $omamori, OmamoriElement $element)
    {
        $this->authorize('update', $omamori);

        $this->service->destroy($omamori, $element);

        return $this->noContent();
    }

    /**
     * 요소 재정렬
     *
     * @param ReorderElementsRequest $request
     * @param Omamori $omamori
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function reorder(ReorderElementsRequest $request, Omamori $omamori)
    {
        $this->authorize('update', $omamori);

        $elementIds = $request->validated()['elementIds'];

        $this->service->reorder($omamori, $elementIds);

        return $this->noContent();
    }
}