<?php

namespace App\Http\Requests\Community\Post;

use Illuminate\Foundation\Http\FormRequest;

class IndexMyBookmarksRequest extends FormRequest
{
    /**
     * 요청 권한
     *
     * 라우트에서 auth:sanctum으로 보호하므로 true.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 유효성 검사 규칙
     *
     * /me/bookmarks?page=&size=
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * 유효성 검사 메시지
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'page는 정수여야 합니다.',
            'page.min' => 'page는 1 이상이어야 합니다.',
            'size.integer' => 'size는 정수여야 합니다.',
            'size.min' => 'size는 1 이상이어야 합니다.',
            'size.max' => 'size는 100 이하여야 합니다.',
        ];
    }

    /**
     * 페이지 사이즈 반환
     *
     * @param int $default
     * @return int
     */
    public function perPage(int $default = 10): int
    {
        return (int) ($this->validated()['size'] ?? $default);
    }
}