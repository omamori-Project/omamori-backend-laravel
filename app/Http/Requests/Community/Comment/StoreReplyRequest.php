<?php

namespace App\Http\Requests\Community\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreReplyRequest extends FormRequest
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
     * 답글 작성 Validation 규칙
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:2000'],
        ];
    }
}