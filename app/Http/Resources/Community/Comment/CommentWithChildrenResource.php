<?php

namespace App\Http\Resources\Community\Comment;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentWithChildrenResource extends CommentResource
{
    /**
     * 댓글(부모) + 답글(children) 포함 리소스 변환
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Comment $comment */
        $comment = $this->resource;

        $data = parent::toArray($request);

        $data['children'] = $comment->relationLoaded('children')
            ? CommentResource::collection($comment->children)
            : [];

        return $data;
    }
}