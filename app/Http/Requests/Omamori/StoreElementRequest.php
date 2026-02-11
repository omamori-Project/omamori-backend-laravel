<?php

namespace App\Http\Requests\Omamori;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $base = [
            'type' => ['required', 'string', Rule::in(['text', 'stamp', 'background'])],
            'props' => ['sometimes', 'array'],
            'transform' => ['sometimes', 'array'],
        ];

        if ($type === 'background') {
            $base['props'] = ['required', 'array']; 
            $base['props.kind'] = ['required', 'string', Rule::in(['solid', 'gradient', 'pattern', 'asset'])];
            $base['props.color'] = ['sometimes', 'string'];
        }

        if ($type === 'stamp') {
            $base['props'] = ['required', 'array'];
            $base['props.asset_key'] = ['required', 'string'];
        }

        return $base;
    }
}