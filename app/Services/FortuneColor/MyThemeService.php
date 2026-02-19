<?php

declare(strict_types=1);

namespace App\Services\FortuneColor;

use App\Models\FortuneColor;
use App\Models\User;
use App\Repositories\FortuneColor\FortuneColorRepository;
use App\Services\BaseService;
use Illuminate\Validation\ValidationException;

class MyThemeService extends BaseService
{
    public function __construct(
        private readonly FortuneColorRepository $fortuneColorRepository,
    ) {
    }

    /**
     * 내 테마(행운컬러) 적용/해제
     *
     * - fortuneColorId가 null이면 해제
     * - 비활성이면 422
     *
     * @param User $user
     * @param int|null $fortuneColorId
     * @return User
     * @throws ValidationException
     */
    public function update(User $user, ?int $fortuneColorId): User
    {
        return $this->transaction(function () use ($user, $fortuneColorId) {
            if ($fortuneColorId === null) {
                $user->forceFill([
                    'applied_fortune_color_id' => null,
                ])->save();

                return $user->refresh();
            }

            /** @var FortuneColor $fortuneColor */
            $fortuneColor = $this->fortuneColorRepository->findOrFail($fortuneColorId);

            if ($fortuneColor->is_active === false) {
                throw ValidationException::withMessages([
                    'fortuneColorId' => ['비활성 행운 컬러는 적용할 수 없습니다.'],
                ]);
            }

            $user->forceFill([
                'applied_fortune_color_id' => $fortuneColor->id,
            ])->save();

            return $user->refresh();
        });
    }
}