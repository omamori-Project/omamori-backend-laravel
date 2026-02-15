<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frame;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frame\FrameIndexRequest;
use App\Http\Resources\Frame\FrameResource;
use App\Services\Frame\FrameService;
use Illuminate\Http\JsonResponse;

class FrameController extends Controller
{
    public function __construct(
        private readonly FrameService $frameService,
    ) {
    }

    /**
     * 프레임 목록 (Public)
     *
     * GET /api/v1/frames
     *
     * @param FrameIndexRequest $request
     * @return JsonResponse
     */
    public function index(FrameIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $page = (int) ($validated['page'] ?? 1);
        $size = (int) ($validated['size'] ?? 20);

        $isActive = array_key_exists('isActive', $validated)
            ? (bool) $validated['isActive']
            : true;

        $paginator = $this->frameService->paginate($page, $size, $isActive);

        return $this->paginated($paginator, FrameResource::class, '성공');
    }
}