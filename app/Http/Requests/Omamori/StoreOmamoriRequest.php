<?php

namespace App\Http\Requests\Omamori;

use Illuminate\Foundation\Http\FormRequest;

class StoreOmamoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                    => ['required', 'string', 'max:120'],
            'meaning'                  => ['nullable', 'string', 'max:500'],
            'theme'                    => ['nullable', 'string', 'max:30'],
            'size_code'                => ['nullable', 'string', 'max:10'],
            'applied_fortune_color_id' => ['nullable', 'exists:fortune_colors,id'],
            'applied_frame_id'         => ['nullable', 'exists:frames,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'                 => '오마모리 제목은 필수입니다.',
            'title.max'                      => '제목은 120자 이내로 입력해주세요.',
            'applied_fortune_color_id.exists' => '유효하지 않은 행운 색상입니다.',
            'applied_frame_id.exists'         => '유효하지 않은 프레임입니다.',
        ];
    }
}