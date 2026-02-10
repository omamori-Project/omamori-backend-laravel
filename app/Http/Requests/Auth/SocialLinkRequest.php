<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SocialLinkRequest extends FormRequest
{
    /**
     * 인가 확인
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 유효성 검증 규칙
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'access_token' => ['required', 'string'],
        ];
    }

    /**
     * 에러 메시지 커스텀
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'access_token.required' => 'OAuth access_token을 전달해주세요.',
        ];
    }
}