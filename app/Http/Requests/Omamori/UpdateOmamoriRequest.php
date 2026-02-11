<?php

namespace App\Http\Requests\Omamori;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOmamoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('omamori')->user_id;
    }

    public function rules(): array
    {
        return [
            'title'                    => ['sometimes', 'string', 'max:120'],
            'meaning'                  => ['nullable', 'string', 'max:500'],
            'theme'                    => ['nullable', 'string', 'max:30'],
            'size_code'                => ['nullable', 'string', 'max:10'],
            'applied_fortune_color_id' => ['sometimes', 'nullable', 'exists:fortune_colors,id'],
            'applied_frame_id'         => ['sometimes', 'nullable', 'exists:frames,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.max'                       => '제목은 120자 이내로 입력해주세요.',
            'applied_fortune_color_id.exists'  => '유효하지 않은 행운 색상입니다.',
            'applied_frame_id.exists'          => '유효하지 않은 프레임입니다.',
        ];
    }
}