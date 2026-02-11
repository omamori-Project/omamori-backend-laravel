<?php

namespace App\Http\Requests\Share;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active'    => ['sometimes', 'boolean'],
            'expires_at'   => ['sometimes', 'nullable', 'date'],
            'rotate_token' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_active.boolean'    => 'is_active는 true/false 값이어야 합니다.',
            'expires_at.date'      => 'expires_at은 올바른 날짜 형식이어야 합니다.',
            'rotate_token.boolean' => 'rotate_token은 true/false 값이어야 합니다.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedPayload(): array
    {
        return $this->validated();
    }
}