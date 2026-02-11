<?php

namespace App\Http\Resources\Omamori;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OmamoriResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'meaning'      => $this->meaning,
            'status'       => $this->status,
            'theme'        => $this->theme,
            'size_code'    => $this->size_code,
            'back_message' => $this->back_message,
            'view_count'   => $this->view_count,
            'published_at' => $this->published_at?->toISOString(),

            // 관계 데이터
            'fortune_color' => $this->whenLoaded('fortuneColor', fn () => [
                'id'            => $this->fortuneColor->id,
                'code'          => $this->fortuneColor->code,
                'name'          => $this->fortuneColor->name,
                'hex'           => $this->fortuneColor->hex,
                'short_meaning' => $this->fortuneColor->short_meaning,
            ]),

            'frame' => $this->whenLoaded('frame', fn () => [
                'id'          => $this->frame->id,
                'name'        => $this->frame->name,
                'frame_key'   => $this->frame->frame_key,
                'preview_url' => $this->frame->preview_url,
            ]),

            'elements' => $this->whenLoaded('elements'),

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}