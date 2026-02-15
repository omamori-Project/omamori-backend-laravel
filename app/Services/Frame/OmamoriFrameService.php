<?php

declare(strict_types=1);

namespace App\Services\Frame;

use App\Models\Frame;
use App\Models\Omamori;
use App\Repositories\Frame\FrameRepository;
use App\Services\BaseService;
use Illuminate\Validation\ValidationException;

class OmamoriFrameService extends BaseService
{
    public function __construct(
        private readonly FrameRepository $frameRepository,
    ) {
    }

    /**
     * 오마모리에 프레임 적용
     *
     * - 존재하지 않으면 404
     * - 비활성이면 422
     *
     * @param Omamori $omamori
     * @param int $frameId
     * @return Omamori
     * @throws ValidationException
     */
    public function apply(Omamori $omamori, int $frameId): Omamori
    {
        return $this->transaction(function () use ($omamori, $frameId) {
            /** @var Frame $frame */
            $frame = $this->frameRepository->findOrFail($frameId);

            if ($frame->is_active === false) {
                throw ValidationException::withMessages([
                    'frameId' => ['비활성 프레임은 적용할 수 없습니다.'],
                ]);
            }

            $omamori->update([
                'applied_frame_id' => $frame->id,
            ]);

            return $omamori->refresh();
        });
    }

}