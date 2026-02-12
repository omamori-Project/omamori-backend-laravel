<?php

namespace App\Http\Resources\Community\Comment;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * 댓글/답글 리소스 변환
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Comment $comment */
        $comment = $this->resource;

        return [
            'id' => $comment->id,
            'post_id' => (int) $comment->post_id,
            'user_id' => (int) $comment->user_id,
            'parent_id' => $comment->parent_id !== null ? (int) $comment->parent_id : null,

            'content' => $comment->content,

            'user' => $comment->relationLoaded('user') && $comment->user !== null
                ? [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ]
                : null,

            'parent' => $comment->relationLoaded('parent') && $comment->parent !== null
                ? [
                    'id' => $comment->parent->id,
                    'user_id' => (int) $comment->parent->user_id,
                    'content' => $comment->parent->content,
                ]
                : null,

            'created_at' => $comment->created_at?->toISOString(),
            'updated_at' => $comment->updated_at?->toISOString(),
        ];
    }
}