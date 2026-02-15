<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Frame;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFrameRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:80'],
            'frameKey' => ['sometimes', 'string', 'max:60'],
            'previewUrl' => ['nullable', 'string'],
            'assetFileId' => ['nullable', 'integer'],
            'meta' => ['nullable', 'array'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}