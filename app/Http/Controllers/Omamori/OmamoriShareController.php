<?php
namespace App\Http\Controllers\Omamori;

use App\Http\Controllers\Controller;
use App\Http\Resources\Share\ShareResource;
use App\Models\Omamori;
use App\Services\Share\ShareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class OmamoriShareController
 *
 * 오마모리 단위 공유 생성/목록 API
 * - POST /api/v1/omamoris/{id}/share
 * - GET  /api/v1/omamoris/{id}/shares
 */
class OmamoriShareController extends Controller
{
    public function __construct(
        private readonly ShareService $shareService,
    ) {
    }

    /**
     * 공유 링크 생성
     *
     * @param Request $request
     * @param int $id omamori id
     * @return JsonResponse
     */
    public function store(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $omamori = Omamori::query()->whereKey($id)->firstOrFail();

        // 공유 생성/목록은 "본인만" 관리 기능 => OmamoriPolicy(update) 사용
        $this->authorize('update', $omamori);

        $share = $this->shareService->createOrGetActiveShare($omamori, $user);

        return $this->created(new ShareResource($share), '공유 링크 생성 완료');
    }

    /**
     * 특정 오마모리의 공유 링크 목록
     *
     * @param Request $request
     * @param int $id omamori id
     * @return JsonResponse
     */
    public function index(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $omamori = Omamori::query()->whereKey($id)->firstOrFail();

        $this->authorize('update', $omamori);

        $shares = $this->shareService->listByOmamori($omamori, $user);

        return $this->success(ShareResource::collection($shares), '공유 링크 목록 조회 성공');
    }
}