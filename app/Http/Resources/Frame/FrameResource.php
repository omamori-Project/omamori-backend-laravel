<?php

declare(strict_types=1);

namespace App\Http\Resources\Frame;

use App\Models\Frame;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Frame
 */
class FrameResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var Frame $frame */
        $frame = $this->resource;

        return [
            'id' => $frame->id,
            'name' => $frame->name,
            'frame_key' => $frame->frame_key,
            'preview_url' => $frame->preview_url,
            'is_active' => (bool) $frame->is_active,
            'meta' => $frame->meta ?? (object) [],
        ];
    }
}