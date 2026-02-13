<?php

namespace App\Http\Controllers\Omamori;

use App\Http\Controllers\Controller;
use App\Http\Requests\Omamori\IndexOmamoriRequest;
use App\Http\Requests\Omamori\StoreOmamoriRequest;
use App\Http\Requests\Omamori\UpdateOmamoriRequest;
use App\Http\Requests\Omamori\UpdateBackMessageRequest;
use App\Http\Resources\Omamori\OmamoriResource;
use App\Services\Omamori\OmamoriService;
use App\Models\Omamori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OmamoriController extends Controller
{
    /**
     * @param OmamoriService $omamoriService
     */
    public function __construct(
        private OmamoriService $omamoriService
    ) {}

    /**
     * 내 오마모리 목록 조회
     * GET /api/v1/omamoris?status={status}&page={page}&size={size}&sort={sort}
     *
     * @param IndexOmamoriRequest $request
     * @return JsonResponse
     */
    public function index(IndexOmamoriRequest $request): JsonResponse
    {
        $paginator = $this->omamoriService->getMyOmamoris(
            $request->user()->id,
            $request->validated()
        );

        return $this->paginated($paginator, OmamoriResource::class);
    }
    

    /**
     * 오마모리 생성
     * POST /api/v1/omamoris
     * 
     * @param StoreOmamoriRequest $request
     * @return JsonResponse
     */
    public function store(StoreOmamoriRequest $request): JsonResponse
    {
        $omamori = $this->omamoriService->createOmamori(
            $request->user()->id,
            $request->validated()
        );

        return $this->created(new OmamoriResource($omamori), '오마모리가 생성되었습니다.');
    }

    /**
     * 오마모리 조회 (편집/확인)
     * GET /api/v1/omamoris/{omamori}
     *
     * @param Omamori $omamori
     * @return JsonResponse
     */
    public function show(Omamori $omamori): JsonResponse
    {
        $this->authorize('view', $omamori);

        $omamori = $this->omamoriService->getOmamori($omamori->id);

        return $this->success(new OmamoriResource($omamori));
    }

    /**
     * 제작 정보 수정
     * PATCH /api/v1/omamoris/{omamori}
     *
     * @param UpdateOmamoriRequest $request
     * @param Omamori $omamori
     * @return JsonResponse
     */
    public function update(UpdateOmamoriRequest $request, Omamori $omamori): JsonResponse
    {
        $this->authorize('update', $omamori);

        $omamori = $this->omamoriService->updateOmamori(
            $omamori,
            $request->validated()
        );

        return $this->success(new OmamoriResource($omamori), '오마모리가 수정되었습니다.');
    }

    /**
     * 뒷면 메시지 입력/수정
     * PATCH /api/v1/omamoris/{omamori}/back-message
     *
     * @param UpdateBackMessageRequest $request
     * @param Omamori $omamori
     * @return JsonResponse
     */
    public function updateBackMessage(UpdateBackMessageRequest $request, Omamori $omamori): JsonResponse
    {
        $this->authorize('update', $omamori);

        $omamori = $this->omamoriService->updateBackMessage(
            $omamori,
            $request->validated('back_message')
        );

        return $this->success(new OmamoriResource($omamori), '뒷면 메시지가 수정되었습니다.');
    }

    /**
     * 오마모리 삭제
     * DELETE /api/v1/omamoris/{omamori}
     *
     * @param Omamori $omamori
     */
    public function destroy(Omamori $omamori): Response
    {
        $this->authorize('delete', $omamori);

        $this->omamoriService->deleteOmamori($omamori);

        return response()->noContent();
    }

    /**
     * 오마모리를 임시 저장(draft) 상태로 유지
     * POST /api/v1/omamoris/{omamoriId}/save-draft
     *
     * @param  Omamori  $omamori
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDraft(Omamori $omamori)
    {
        $this->authorize('update', $omamori);
    
        $updated = $this->omamoriService->saveDraft($omamori);
    
        return $this->success(new OmamoriResource($updated), 'Draft saved.');
    }
    
    /**
     * 오마모리를 최종 발행(published) 상태로 전환
     * POST /api/v1/omamoris/{omamoriId}/publish
     *
     * @param  Omamori  $omamori
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(Omamori $omamori)
    {
        $this->authorize('update', $omamori);
    
        $published = $this->omamoriService->publish($omamori);
    
        return $this->success(new OmamoriResource($published), 'Published.');
    }

}