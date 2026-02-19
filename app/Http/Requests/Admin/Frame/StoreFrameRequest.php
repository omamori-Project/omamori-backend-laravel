<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Frame;

use Illuminate\Foundation\Http\FormRequest;

class StoreFrameRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:80'],
            'frameKey' => ['required', 'string', 'max:60'],
            'previewUrl' => ['nullable', 'string'],
            'assetFileId' => ['nullable', 'integer'],
            'meta' => ['nullable', 'array'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}