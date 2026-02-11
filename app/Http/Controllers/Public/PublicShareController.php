<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Share\PublicShareResource;
use App\Services\Share\ShareService;
use Illuminate\Http\JsonResponse;

class PublicShareController extends Controller
{
    public function __construct(
        private readonly ShareService $shareService,
    ) {
    }

    public function show(string $token): JsonResponse
    {
        $share = $this->shareService->resolvePublic($token);

        // OmamoriResource whenLoaded 만족시키기 위한 eager load
        $share->loadMissing([
            'omamori.elements',
            'omamori.fortuneColor',
            'omamori.frame',
        ]);

        return $this->success(new PublicShareResource($share), '공유 오마모리 조회 성공');
    }
}