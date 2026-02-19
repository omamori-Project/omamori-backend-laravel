<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Frame;

use Illuminate\Foundation\Http\FormRequest;

class AdminFrameIndexRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'isActive' => ['sometimes', 'boolean'],
            'withTrashed' => ['sometimes', 'boolean'],
            'onlyTrashed' => ['sometimes', 'boolean'],
        ];
    }
}