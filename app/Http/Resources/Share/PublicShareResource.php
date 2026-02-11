<?php

namespace App\Http\Resources\Share;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Omamori\OmamoriResource;

/**
 * Class PublicShareResource
 *
 * 공개 공유 조회 응답 리소스
 * - share 정보 + omamori 상세(요소 포함)
 */
class PublicShareResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'share'  => new ShareResource($this->resource),
            'omamori'=> new OmamoriResource($this->resource->omamori),
        ];
    }
}