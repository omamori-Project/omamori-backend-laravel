<?php

namespace App\Http\Requests\Omamori;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBackMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('omamori')->user_id;
    }

    public function rules(): array
    {
        return [
            'back_message' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'back_message.required' => '뒷면 메시지를 입력해주세요.',
            'back_message.max'      => '메시지는 500자 이내로 입력해주세요.',
        ];
    }
}