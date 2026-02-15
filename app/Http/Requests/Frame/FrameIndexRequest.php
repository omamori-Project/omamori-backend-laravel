<?php

declare(strict_types=1);

namespace App\Http\Requests\Frame;

use Illuminate\Foundation\Http\FormRequest;

class FrameIndexRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * - isActive: 1/0 (기본: 1)
     * - page, size: pagination
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'page는 정수여야 합니다.',
            'page.min' => 'page는 1 이상이어야 합니다.',
            'size.integer' => 'size는 정수여야 합니다.',
            'size.min' => 'size는 1 이상이어야 합니다.',
            'size.max' => 'size는 100 이하여야 합니다.',
            'isActive.boolean' => 'isActive는 boolean 값이어야 합니다.',
        ];
    }
}