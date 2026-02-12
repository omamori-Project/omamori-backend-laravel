<?php

namespace App\Http\Requests\Community\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPostRequest extends FormRequest
{
    /**
     * 공개 목록 조회는 누구나 가능
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 게시글 목록 Query Validation 규칙
     *
     * 지원 파라미터:
     * - page: 페이지 번호 (기본 1)
     * - size: 페이지 크기 (기본 10, 최대 100)
     * - sort: latest | oldest | popular
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['latest', 'oldest', 'popular'])],
        ];
    }

    /**
     * Validation 에러 메시지 정의
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page.integer' => '페이지는 정수여야 합니다.',
            'page.min' => '페이지는 최소 1이어야 합니다.',
            'size.integer' => '페이지 크기는 정수여야 합니다.',
            'size.min' => '페이지 크기는 최소 1이어야 합니다.',
            'size.max' => '페이지 크기는 100 이하여야 합니다.',
            'sort.in' => '정렬 기준은 latest, oldest, popular 중 하나여야 합니다.',
        ];
    }
}