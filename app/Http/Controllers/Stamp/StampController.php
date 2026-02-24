<?php

namespace App\Http\Controllers\Stamp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stamp\IndexStampRequest;
use App\Services\Stamp\StampService;
use Illuminate\Http\JsonResponse;

class StampController extends Controller
{
    /**
     * @param  StampService  $stampCatalogService
     */
    public function __construct(
        private readonly StampService $stampCatalogService
    ) {}

    /**
     * 스탬프 목록 조회.
     *
     * GET /api/v1/stamps
     *
     * @param  IndexStampRequest  $request
     * @return JsonResponse
     */
    public function index(IndexStampRequest $request): JsonResponse
    {
        $paginator = $this->stampCatalogService->paginate(
            $request->validated()
        );

        return $this->success($paginator, '성공');
    }
}