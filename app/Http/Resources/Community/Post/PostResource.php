<?php

namespace App\Http\Resources\Community\Post;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * 게시글 리소스 변환
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Post $post */
        $post = $this->resource;

        return [
            'id' => $post->id,
            'user_id' => $post->user_id,
            'omamori_id' => $post->omamori_id,

            'title' => $post->title,
            'content' => $post->content,

            'like_count' => (int) $post->like_count,
            'comment_count' => (int) $post->comment_count,
            'bookmark_count' => (int) $post->bookmark_count,
            'view_count' => (int) $post->view_count,

            'user' => $post->relationLoaded('user') && $post->user !== null
                ? [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                ]
                : null,

            'omamori' => $post->relationLoaded('omamori') && $post->omamori !== null
                ? [
                    'id' => $post->omamori->id,
                    'status' => $post->omamori->status ?? null,
                    'published_at' => $post->omamori->published_at?->toISOString(),
                ]
                : null,

            'created_at' => $post->created_at?->toISOString(),
            'updated_at' => $post->updated_at?->toISOString(),
        ];
    }
}