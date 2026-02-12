<?php

namespace App\Http\Controllers\Omamori;

use App\Http\Controllers\Controller;
use App\Http\Requests\Omamori\ExportOmamoriRequest;
use App\Http\Resources\Omamori\ExportResultResource;
use App\Services\Omamori\OmamoriExportService;
use Illuminate\Http\JsonResponse;

/**
 * 오마모리 내보내기(Export) API 컨트롤러
 */
class OmamoriExportController extends Controller
{
    public function __construct(
        private readonly OmamoriExportService $exportService
    ) {}

    /**
     * 오마모리 내보내기(다운로드 URL 반환)
     *
     * POST /api/v1/omamoris/{omamoriId}/export
     *
     * @param ExportOmamoriRequest $request
     * @param int $omamoriId
     * @return JsonResponse
     */
    public function export(ExportOmamoriRequest $request, int $omamoriId): JsonResponse
    {
        $file = $this->exportService->export(
            userId: (int) $request->user()->id,
            omamoriId: $omamoriId,
            options: $request->options(),
        );

        return $this->success(new ExportResultResource($file));
    }
}