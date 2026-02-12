<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Share\PublicShareResource;
use App\Http\Resources\Share\SharePreviewResource;
use App\Services\Share\ShareService;
use Illuminate\Http\JsonResponse;

/**
 * 공개 공유 링크(비로그인) 컨트롤러
 * - show(): 공유 오마모리 상세 조회
 * - preview(): 공유 오마모리 "미리보기 카드"
 */
class PublicShareController extends Controller
{
    public function __construct(
        private readonly ShareService $shareService,
    ) {}

    /**
     * 공유 토큰으로 공개 오마모리 조회(상세)
     *
     * GET /api/v1/public/shares/{token}
     *
     * 정책:
     * - 유효한 share 토큰이어야 함
     * - 연결된 omamori는 published여야 함
     *
     * @param string $token
     * @return JsonResponse
     */
    public function show(string $token): JsonResponse
    {
        $share = $this->shareService->resolvePublic($token);

        // OmamoriResource/PublicShareResource whenLoaded 만족시키기 위한 eager load
        $share->loadMissing([
            'omamori.elements',
            'omamori.fortuneColor',
            'omamori.frame',
        ]);

        return $this->success(new PublicShareResource($share), '공유 오마모리 조회 성공');
    }

    /**
     * 공유 토큰으로 미리보기 카드 조회
     *
     * GET /api/v1/public/shares/{token}/preview
     *
     * 정책:
     * - 유효한 share 토큰이어야 함
     * - 연결된 omamori는 published여야 함
     * - 응답은 카드용 최소 데이터만 반환(SharePreviewResource)
     *
     * @param string $token
     * @return JsonResponse
     */
    public function preview(string $token): JsonResponse
    {
        $share = $this->shareService->resolvePublic($token);

        // preview 카드에 필요한 관계만 최소 로딩
        $share->loadMissing([
            'omamori',
            'omamori.fortuneColor',
            'omamori.frame',
        ]);

        return $this->success(new SharePreviewResource($share), '공유 오마모리 미리보기 조회 성공');
    }
}