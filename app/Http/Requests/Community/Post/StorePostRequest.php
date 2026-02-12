<?php

namespace App\Http\Requests\Community\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 게시글 작성 Validation 규칙
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'omamori_id' => ['nullable', 'integer', 'exists:omamoris,id'],
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
            'title.required' => '타이틀은 필수 항목입니다.',
            'title.max' => '타이틀은 150자를 초과할 수 없습니다.',
            'content.required' => '내용은 필수 항목입니다.',
            'omamori_id.exists' => '오마모리 ID가 유효하지 않습니다.',
        ];
    }
}