<?php

declare(strict_types=1);

namespace App\Http\Controllers\FortuneColor;

use App\Http\Controllers\Controller;
use App\Http\Requests\FortuneColor\FortuneColorIndexRequest;
use App\Http\Requests\FortuneColor\FortuneColorTodayRequest;
use App\Http\Resources\FortuneColor\FortuneColorResource;
use App\Models\FortuneColor;
use App\Services\FortuneColor\FortuneColorService;
use Illuminate\Http\JsonResponse;

class FortuneColorController extends Controller
{
    public function __construct(
        private readonly FortuneColorService $fortuneColorService,
    ) {
    }

    /**
     * 행운컬러 목록 (Public)
     *
     * GET /api/v1/fortune-colors
     *
     * @param FortuneColorIndexRequest $request
     * @return JsonResponse
     */
    public function index(FortuneColorIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $page = (int) ($validated['page'] ?? 1);
        $size = (int) ($validated['size'] ?? 20);

        $isActive = array_key_exists('isActive', $validated)
            ? (bool) $validated['isActive']
            : true;

        $paginator = $this->fortuneColorService->paginate($page, $size, $isActive);

        return $this->paginated($paginator, FortuneColorResource::class, '성공');
    }

    /**
     * 행운컬러 단건 조회 (Public)
     *
     * GET /api/v1/fortune-colors/{fortuneColor}
     *
     * @param FortuneColor $fortuneColor
     * @return JsonResponse
     */
    public function show(FortuneColor $fortuneColor): JsonResponse
    {
        return $this->success(new FortuneColorResource($fortuneColor), '성공');
    }

    /**
     * 오늘의 행운컬러 (Public)
     *
     * POST /api/v1/fortune-colors/today
     * body: { "birthday": "YYYY-MM-DD" }
     *
     * @param FortuneColorTodayRequest $request
     * @return JsonResponse
     */
    public function today(FortuneColorTodayRequest $request): JsonResponse
    {
        $birthday = (string) $request->validated()['birthday'];

        $fortuneColor = $this->fortuneColorService->recommendToday($birthday);

        return $this->success(new FortuneColorResource($fortuneColor), '성공');
    }
}