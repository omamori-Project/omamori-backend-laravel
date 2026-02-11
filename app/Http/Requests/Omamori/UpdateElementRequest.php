<?php

namespace App\Http\Requests\Omamori;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['prohibited'], // type 변경 불가
            'layer' => ['prohibited'], // layer 변경은 reorder에서만
            'props' => ['sometimes', 'array'],
            'transform' => ['sometimes', 'array'],
        ];
    }
}