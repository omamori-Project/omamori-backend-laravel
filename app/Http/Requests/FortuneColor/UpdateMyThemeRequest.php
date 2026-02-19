<?php

declare(strict_types=1);

namespace App\Http\Requests\FortuneColor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMyThemeRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * fortuneColorId:
     * - null이면 해제(= 삭제 PATCH)
     * - 값이 있으면 fortune_colors.id 존재 + soft delete 제외
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fortuneColorId' => [
                'nullable',
                'integer',
                Rule::exists('fortune_colors', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fortuneColorId.integer' => 'fortuneColorId는 정수여야 합니다.',
            'fortuneColorId.exists' => '존재하지 않는 행운 컬러입니다.',
        ];
    }
}