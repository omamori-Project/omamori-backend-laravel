<?php

namespace App\Http\Controllers\Omamori;

use App\Http\Controllers\Controller;
use App\Http\Requests\Share\UpdateShareRequest;
use App\Http\Resources\Share\ShareResource;
use App\Models\Share;
use App\Services\Share\ShareService;
use Illuminate\Http\JsonResponse;
/**
 * Class ShareController
 *
 * 공유 설정 변경/삭제 API
 * - PATCH  /api/v1/shares/{shareId}
 * - DELETE /api/v1/shares/{shareId}
 */
class ShareController extends Controller
{
    public function __construct(
        private readonly ShareService $shareService,
    ) {
    }

    /**
     * 공유 설정 변경
     *
     * @param UpdateShareRequest $request
     * @param int $shareId
     * @return JsonResponse
     */
    public function update(UpdateShareRequest $request, int $shareId): JsonResponse
    {
        $share = Share::query()->whereKey($shareId)->firstOrFail();

        $this->authorize('update', $share);

        $updated = $this->shareService->updateShare($share, $request->validatedPayload());

        return $this->success(new ShareResource($updated), '공유 설정 수정 완료');
    }

    /**
     * 공유 삭제(soft delete)
     *
     * @param int $shareId
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $shareId): \Illuminate\Http\Response
    {
        $share = Share::query()->whereKey($shareId)->firstOrFail();

        $this->authorize('delete', $share);

        $this->shareService->deleteShare($share);

        return $this->noContent();
    }
}