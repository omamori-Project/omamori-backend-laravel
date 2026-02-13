<?php

namespace App\Http\Requests\Community\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'omamori_id' => ['required', 'integer', 'exists:omamoris,id'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => '타이틀은 필수 항목입니다.',
            'title.max' => '타이틀은 150자를 초과할 수 없습니다.',
            'content.required' => '본문은 필수 항목입니다.',
            'omamori_id.required' => '오마모리는 필수 항목입니다.',
            'omamori_id.exists' => '존재하지 않는 오마모리입니다.',
            'tags.array' => '태그 형식이 올바르지 않습니다.',
            'tags.max' => '태그는 최대 10개까지 가능합니다.',
            'tags.*.max' => '태그는 최대 20자까지 가능합니다.',
        ];
    }
}