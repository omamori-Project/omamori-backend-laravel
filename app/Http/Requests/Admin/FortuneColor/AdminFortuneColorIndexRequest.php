<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\FortuneColor;

use Illuminate\Foundation\Http\FormRequest;

class AdminFortuneColorIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * - withTrashed, onlyTrashed 지원
     *
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