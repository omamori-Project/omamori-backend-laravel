<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Frame\AdminFrameIndexRequest;
use App\Http\Requests\Admin\Frame\StoreFrameRequest;
use App\Http\Requests\Admin\Frame\UpdateFrameRequest;
use App\Http\Resources\Frame\FrameResource;
use App\Models\Frame;
use App\Repositories\Frame\FrameRepository;
use Illuminate\Http\JsonResponse;

class FrameAdminController extends Controller
{
    public function __construct(
        private readonly FrameRepository $frameRepository,
    ) {
    }

    /**
     * 프레임 목록 (Admin)
     *
     * GET /api/v1/admin/frames
     *
     * @param AdminFrameIndexRequest $request
     * @return JsonResponse
     */
    public function index(AdminFrameIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Frame::class);

        $v = $request->validated();

        $page = (int) ($v['page'] ?? 1);
        $size = (int) ($v['size'] ?? 20);

        $isActive = array_key_exists('isActive', $v) ? (bool) $v['isActive'] : null;
        $withTrashed = (bool) ($v['withTrashed'] ?? false);
        $onlyTrashed = (bool) ($v['onlyTrashed'] ?? false);

        $paginator = $this->frameRepository->paginateAdmin(
            page: $page,
            size: $size,
            isActive: $isActive,
            withTrashed: $withTrashed,
            onlyTrashed: $onlyTrashed,
        );

        return $this->paginated($paginator, FrameResource::class, '성공');
    }

    /**
     * 프레임 생성 (Admin)
     *
     * POST /api/v1/admin/frames
     *
     * @param StoreFrameRequest $request
     * @return JsonResponse
     */
    public function store(StoreFrameRequest $request): JsonResponse
    {
        $this->authorize('create', Frame::class);

        $v = $request->validated();

        $frame = $this->frameRepository->create([
            'name' => $v['name'],
            'frame_key' => $v['frameKey'],
            'preview_url' => $v['previewUrl'] ?? null,
            'asset_file_id' => $v['assetFileId'] ?? null,
            'meta' => $v['meta'] ?? [],
            'is_active' => $v['isActive'] ?? true,
        ]);

        return $this->created(new FrameResource($frame), '생성 완료');
    }

    /**
     * 프레임 수정 (Admin)
     *
     * PATCH /api/v1/admin/frames/{frame}
     *
     * @param UpdateFrameRequest $request
     * @param Frame $frame
     * @return JsonResponse
     */
    public function update(UpdateFrameRequest $request, Frame $frame): JsonResponse
    {
        $this->authorize('update', $frame);

        $v = $request->validated();

        $frame = $this->frameRepository->update($frame, [
            'name' => $v['name'] ?? $frame->name,
            'frame_key' => $v['frameKey'] ?? $frame->frame_key,
            'preview_url' => array_key_exists('previewUrl', $v) ? ($v['previewUrl'] ?? null) : $frame->preview_url,
            'asset_file_id' => array_key_exists('assetFileId', $v) ? ($v['assetFileId'] ?? null) : $frame->asset_file_id,
            'meta' => array_key_exists('meta', $v) ? ($v['meta'] ?? []) : $frame->meta,
            'is_active' => $v['isActive'] ?? $frame->is_active,
        ]);

        return $this->success(new FrameResource($frame), '성공');
    }

    /**
     * 프레임 삭제 (Admin) - Soft Delete
     *
     * DELETE /api/v1/admin/frames/{frame}
     *
     * @param Frame $frame
     * @return JsonResponse
     */
    public function destroy(Frame $frame): JsonResponse
    {
        $this->authorize('delete', $frame);

        $this->frameRepository->delete($frame);

        return $this->noContent();
    }
}