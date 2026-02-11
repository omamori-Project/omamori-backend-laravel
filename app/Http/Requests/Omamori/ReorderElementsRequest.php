<?php

namespace App\Http\Requests\Omamori;

use Illuminate\Foundation\Http\FormRequest;

class ReorderElementsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'elementIds' => ['required', 'array', 'min:1'],
            'elementIds.*' => ['required', 'integer', 'distinct'],
        ];
    }
}