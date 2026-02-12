<?php

namespace App\Http\Resources\Share;

use App\Models\Omamori;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SharePreviewResource
 *
 * 공개 미리보기 카드 리소스
 * - 공개 응답이므로 최소 정보만 반환
 *
 * @property string $token
 * @property int $view_count
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|string $created_at
 */
class SharePreviewResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Omamori|null $omamori */
        $omamori = $this->resource->relationLoaded('omamori')
            ? $this->resource->omamori
            : null;

        return [
            'token'      => $this->token,
            'view_count' => $this->view_count,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,

            'omamori' => $omamori ? [
                'id'            => $omamori->id,
                'status'        => $omamori->status,

                // 있으면 내려주고, 없으면 null
                'fortune_color' => $omamori->relationLoaded('fortuneColor') && $omamori->fortuneColor
                    ? [
                        'id'   => $omamori->fortuneColor->id,
                        'name' => $omamori->fortuneColor->name ?? null,
                        'hex'  => $omamori->fortuneColor->hex ?? null,
                    ]
                    : null,

                'frame' => $omamori->relationLoaded('frame') && $omamori->frame
                    ? [
                        'id'   => $omamori->frame->id,
                        'name' => $omamori->frame->name ?? null,
                    ]
                    : null,

                'updated_at' => $omamori->updated_at ?? null,
            ] : null,
        ];
    }
}