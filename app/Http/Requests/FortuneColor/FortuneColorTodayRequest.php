<?php

declare(strict_types=1);

namespace App\Http\Requests\FortuneColor;

use Illuminate\Foundation\Http\FormRequest;

class FortuneColorTodayRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * - birthday: YYYY-MM-DD
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'birthday' => ['required', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'birthday.required' => 'birthday는 필수입니다.',
            'birthday.date_format' => 'birthday 형식이 올바르지 않습니다. (YYYY-MM-DD)',
        ];
    }
}