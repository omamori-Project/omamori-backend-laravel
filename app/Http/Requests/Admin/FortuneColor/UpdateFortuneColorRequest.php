<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\FortuneColor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFortuneColorRequest extends FormRequest
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
            'code' => ['sometimes', 'string', 'max:60'],
            'name' => ['sometimes', 'string', 'max:60'],
            'hex' => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'category' => ['nullable', 'string', 'max:30'],
            'shortMeaning' => ['nullable', 'string', 'max:120'],
            'meaning' => ['nullable', 'string'],
            'tips' => ['nullable', 'array'],
            'tips.*' => ['string'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}