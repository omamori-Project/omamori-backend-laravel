<?php

namespace App\Http\Resources\Community\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookmarkedPostResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $post = $this->post;

        return [
            'bookmarked_at' => $this->created_at?->toISOString(),

            'post' => [
                'id' => $post?->id,
                'title' => $post?->title,
                'content' => $post?->content,
                'created_at' => $post?->created_at?->toISOString(),
                'updated_at' => $post?->updated_at?->toISOString(),
                'view_count' => $post?->view_count,
                'comment_count' => $post?->comment_count,
                'like_count' => $post?->like_count ?? null,
            ],
        ];
    }
}