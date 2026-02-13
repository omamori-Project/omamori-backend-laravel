<?php

namespace App\Services\Community;

use App\Models\Omamori;

class PostOmamoriSnapshotService
{
    /**
     * 오마모리 스냅샷 생성
     *
     * @param Omamori $omamori
     * @return array<string, mixed>
     */
    public function make(Omamori $omamori): array
    {
        $omamori->loadMissing([
            'fortuneColor',
            'frame',
            'elements',
            'previewFile',
        ]);

        return [
            'id' => $omamori->id,
            'title' => $omamori->title,
            'meaning' => $omamori->meaning,
            'status' => $omamori->status,
            'theme' => $omamori->theme,
            'size_code' => $omamori->size_code,
            'back_message' => $omamori->back_message,
            'published_at' => $omamori->published_at,

            'fortune_color' => $omamori->fortuneColor ? [
                'id' => $omamori->fortuneColor->id,
                'code' => $omamori->fortuneColor->code,
                'name' => $omamori->fortuneColor->name,
                'hex' => $omamori->fortuneColor->hex,
                'short_meaning' => $omamori->fortuneColor->short_meaning,
            ] : null,

            'frame' => $omamori->frame ? [
                'id' => $omamori->frame->id,
                'name' => $omamori->frame->name,
                'frame_key' => $omamori->frame->frame_key,
                'preview_url' => $omamori->frame->preview_url,
            ] : null,

            'preview' => $omamori->previewFile ? [
                'id' => $omamori->previewFile->id,
                'url' => $omamori->previewFile->url ?? null,
                'path' => $omamori->previewFile->path ?? null,
            ] : null,

            'elements' => $omamori->elements->map(static fn($e) => [
                'id' => $e->id,
                'type' => $e->type,
                'layer' => $e->layer,
                'props' => $e->props,
                'transform' => $e->transform,
            ])->values()->all(),
        ];
    }
}