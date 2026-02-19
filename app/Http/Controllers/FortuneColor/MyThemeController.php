<?php

declare(strict_types=1);

namespace App\Http\Controllers\FortuneColor;

use App\Http\Controllers\Controller;
use App\Http\Requests\FortuneColor\UpdateMyThemeRequest;
use App\Http\Resources\FortuneColor\FortuneColorResource;
use App\Models\FortuneColor;
use App\Services\FortuneColor\MyThemeService;
use Illuminate\Http\JsonResponse;

class MyThemeController extends Controller
{
    public function __construct(
        private readonly MyThemeService $myThemeService,
    ) {
    }

    /**
     * 내 테마(행운컬러) 적용/해제
     *
     * PATCH /api/v1/me/theme
     */
    public function update(UpdateMyThemeRequest $request): JsonResponse
    {
        $user = $request->user();

        $fortuneColorId = $request->validated()['fortuneColorId'] ?? null;
        $fortuneColorId = $fortuneColorId === null ? null : (int) $fortuneColorId;

        $user = $this->myThemeService->update($user, $fortuneColorId);

        $fortuneColor = null;
        if ($user->applied_fortune_color_id !== null) {
            $fortuneColor = FortuneColor::query()
                ->whereKey($user->applied_fortune_color_id)
                ->first();
        }

        return $this->success([
            'applied_fortune_color_id' => $user->applied_fortune_color_id,
            'fortune_color' => $fortuneColor ? new FortuneColorResource($fortuneColor) : null,
        ], '성공');
    }
}