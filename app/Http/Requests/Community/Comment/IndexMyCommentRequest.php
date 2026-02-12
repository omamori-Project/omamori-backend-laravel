<?php

namespace App\Http\Requests\Community\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexMyCommentRequest extends FormRequest
{
    /**
     * 로그인 사용자만 가능
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 내 댓글 목록 Query Validation 규칙
     *
     * 지원:
     * - page
     * - size
     * - sort: latest|oldest
     * - type: comment|reply
     * - postId: 특정 게시글 필터
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['latest', 'oldest'])],
            'type' => ['sometimes', 'string', Rule::in(['comment', 'reply'])],
            'postId' => ['sometimes', 'integer', 'exists:posts,id'],
        ];
    }
}