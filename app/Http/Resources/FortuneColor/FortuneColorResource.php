<?php

declare(strict_types=1);

namespace App\Http\Resources\FortuneColor;

use App\Models\FortuneColor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FortuneColor
 */
class FortuneColorResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var FortuneColor $fortuneColor */
        $fortuneColor = $this->resource;

        return [
            'id' => $fortuneColor->id,
            'code' => $fortuneColor->code,
            'name' => $fortuneColor->name,
            'hex' => $fortuneColor->hex,
            'category' => $fortuneColor->category,
            'short_meaning' => $fortuneColor->short_meaning,
            'meaning' => $fortuneColor->meaning,
            'tips' => $fortuneColor->tips ?? [],
            'is_active' => (bool) $fortuneColor->is_active,
        ];
    }
}