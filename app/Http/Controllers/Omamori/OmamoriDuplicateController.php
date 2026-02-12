<?php

namespace App\Http\Controllers\Omamori;

use App\Http\Controllers\Controller;
use App\Http\Requests\Omamori\DuplicateOmamoriRequest;
use App\Http\Resources\Omamori\OmamoriResource;
use App\Services\Omamori\OmamoriDuplicateService;
use Illuminate\Http\JsonResponse;

/**
 * 오마모리 복제 API 컨트롤러
 * - 로그인 필요(auth:sanctum)
 * - 복제 후 생성된 Omamori를 반환
 */
class OmamoriDuplicateController extends Controller
{
    public function __construct(
        private readonly OmamoriDuplicateService $duplicateService
    ) {}

    /**
     * 오마모리 복제
     *
     * POST /api/v1/omamoris/{omamoriId}/duplicate
     *
     * @param DuplicateOmamoriRequest $request
     * @param int $omamoriId
     * @return JsonResponse
     */
    public function duplicate(DuplicateOmamoriRequest $request, int $omamoriId): JsonResponse
    {
        $omamori = $this->duplicateService->duplicate(
            userId: (int) $request->user()->id,
            omamoriId: $omamoriId
        );

        return $this->created(new OmamoriResource($omamori));
    }
}