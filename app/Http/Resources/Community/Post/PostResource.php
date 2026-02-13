<?php

namespace App\Http\Resources\Community\Post;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
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

            'omamori_snapshot' => $post->omamori_snapshot,
            'tags' => $post->tags,
            'hidden_at' => $post->hidden_at,

            'like_count' => (int) $post->like_count,
            'comment_count' => (int) $post->comment_count,
            'bookmark_count' => (int) $post->bookmark_count,
            'view_count' => (int) $post->view_count,

            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,

            'user' => $this->whenLoaded('user'),
            'omamori' => $this->whenLoaded('omamori'),
        ];
    }
}