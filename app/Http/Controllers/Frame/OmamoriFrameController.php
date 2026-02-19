<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frame;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frame\ApplyOmamoriFrameRequest;
use App\Models\Omamori;
use App\Services\Frame\OmamoriFrameService;
use Illuminate\Http\JsonResponse;

class OmamoriFrameController extends Controller
{
    public function __construct(
        private readonly OmamoriFrameService $omamoriFrameService,
    ) {
    }

    /**
     * 오마모리에 프레임 적용
     *
     * POST /api/v1/omamoris/{omamori}/frame
     *
     * @param ApplyOmamoriFrameRequest $request
     * @param Omamori $omamori
     * @return JsonResponse
     */
    public function store(ApplyOmamoriFrameRequest $request, Omamori $omamori): JsonResponse
    {
        $this->authorize('update', $omamori);

        $frameId = (int) $request->validated()['frameId'];

        $omamori = $this->omamoriFrameService->apply($omamori, $frameId);

        return $this->success([
            'id' => $omamori->id,
            'applied_frame_id' => $omamori->applied_frame_id,
        ], '성공');
    }
}