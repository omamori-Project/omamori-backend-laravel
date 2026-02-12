<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 공개 미리보기 카드 응답 리소스
 * @property string $token
 * @property int $omamori_id
 * @property int $view_count
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|string $created_at
 *
 * @property-read \App\Models\Omamori|null $omamori
 */
class SharePreviewResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Omamori|null $omamori */
        $omamori = $this->resource->relationLoaded('omamori')
        ? $this->resource->omamori
        : null;

        return [
            'token'      => $this->token,
            'view_count' => $this->view_count,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,

            /**
             * 카드용 오마모리 최소 정보             
             * 
            */
            'omamori' => $omamori ? [
                'id'           => $omamori->id,
                'status'       => $omamori->status, 
                'fortune_color' => $omamori->fortune_color ?? null,
                'frame'        => $omamori->frame ?? null,
                'preview_file' => $omamori->preview_file ?? null,
                'updated_at'   => $omamori->updated_at ?? null,
            ] : null,
        ];
    }
}