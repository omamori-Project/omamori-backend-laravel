<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\FortuneColor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFortuneColorRequest extends FormRequest
{
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
            'code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:60'],
            'hex' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'category' => ['nullable', 'string', 'max:30'],
            'shortMeaning' => ['nullable', 'string', 'max:120'],
            'meaning' => ['nullable', 'string'],
            'tips' => ['nullable', 'array'],
            'tips.*' => ['string'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hex.regex' => 'hex는 #RRGGBB 형식이어야 합니다.',
            'tips.array' => 'tips는 배열이어야 합니다.',
            'tips.*.string' => 'tips의 각 값은 문자열이어야 합니다.',
        ];
    }
}