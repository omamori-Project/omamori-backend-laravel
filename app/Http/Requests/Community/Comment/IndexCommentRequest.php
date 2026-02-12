<?php

namespace App\Http\Requests\Community\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCommentRequest extends FormRequest
{
    /**
     * 공개 댓글 조회 가능
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 댓글 목록 Query Validation 규칙
     *
     * 지원:
     * - page
     * - size (기본 20, 최대 100)
     * - sort: latest|oldest
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['latest', 'oldest'])],
        ];
    }
}