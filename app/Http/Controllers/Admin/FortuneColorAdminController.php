<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FortuneColor\AdminFortuneColorIndexRequest;
use App\Http\Requests\Admin\FortuneColor\StoreFortuneColorRequest;
use App\Http\Requests\Admin\FortuneColor\UpdateFortuneColorRequest;
use App\Http\Resources\FortuneColor\FortuneColorResource;
use App\Models\FortuneColor;
use App\Repositories\FortuneColor\FortuneColorRepository;
use Illuminate\Http\JsonResponse;

class FortuneColorAdminController extends Controller
{
    public function __construct(
        private readonly FortuneColorRepository $fortuneColorRepository,
    ) {
    }

    /**
     * 행운컬러 목록 (Admin)
     *
     * GET /api/v1/admin/fortune-colors
     *
     * @param AdminFortuneColorIndexRequest $request
     * @return JsonResponse
     */
    public function index(AdminFortuneColorIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', FortuneColor::class);

        $v = $request->validated();

        $page = (int) ($v['page'] ?? 1);
        $size = (int) ($v['size'] ?? 20);

        $isActive = array_key_exists('isActive', $v) ? (bool) $v['isActive'] : null;
        $withTrashed = (bool) ($v['withTrashed'] ?? false);
        $onlyTrashed = (bool) ($v['onlyTrashed'] ?? false);

        $paginator = $this->fortuneColorRepository->paginateAdmin(
            page: $page,
            size: $size,
            isActive: $isActive,
            withTrashed: $withTrashed,
            onlyTrashed: $onlyTrashed,
        );

        return $this->paginated($paginator, FortuneColorResource::class, '성공');
    }

    /**
     * 행운컬러 생성 (Admin)
     *
     * POST /api/v1/admin/fortune-colors
     *
     * @param StoreFortuneColorRequest $request
     * @return JsonResponse
     */
    public function store(StoreFortuneColorRequest $request): JsonResponse
    {
        $this->authorize('create', FortuneColor::class);

        $v = $request->validated();

        $fortuneColor = $this->fortuneColorRepository->create([
            'code' => $v['code'],
            'name' => $v['name'],
            'hex' => $v['hex'],
            'category' => $v['category'] ?? null,
            'short_meaning' => $v['shortMeaning'] ?? null,
            'meaning' => $v['meaning'] ?? null,
            'tips' => $v['tips'] ?? [],
            'is_active' => $v['isActive'] ?? true,
        ]);

        return $this->created(new FortuneColorResource($fortuneColor), '생성 완료');
    }

    /**
     * 행운컬러 수정 (Admin)
     *
     * PATCH /api/v1/admin/fortune-colors/{fortuneColor}
     *
     * @param UpdateFortuneColorRequest $request
     * @param FortuneColor $fortuneColor
     * @return JsonResponse
     */
    public function update(UpdateFortuneColorRequest $request, FortuneColor $fortuneColor): JsonResponse
    {
        $this->authorize('update', $fortuneColor);

        $v = $request->validated();

        $fortuneColor = $this->fortuneColorRepository->update($fortuneColor, [
            'code' => $v['code'] ?? $fortuneColor->code,
            'name' => $v['name'] ?? $fortuneColor->name,
            'hex' => $v['hex'] ?? $fortuneColor->hex,
            'category' => $v['category'] ?? $fortuneColor->category,
            'short_meaning' => $v['shortMeaning'] ?? $fortuneColor->short_meaning,
            'meaning' => $v['meaning'] ?? $fortuneColor->meaning,
            'tips' => $v['tips'] ?? $fortuneColor->tips,
            'is_active' => $v['isActive'] ?? $fortuneColor->is_active,
        ]);

        return $this->success(new FortuneColorResource($fortuneColor), '성공');
    }

    /**
     * 행운컬러 삭제 (Admin) - Soft Delete
     *
     * DELETE /api/v1/admin/fortune-colors/{fortuneColor}
     *
     * @param FortuneColor $fortuneColor
     * @return JsonResponse
     */
    public function destroy(FortuneColor $fortuneColor): JsonResponse
    {
        $this->authorize('delete', $fortuneColor);

        $this->fortuneColorRepository->delete($fortuneColor);

        return $this->noContent();
    }
}