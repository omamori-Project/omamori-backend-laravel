<?php

declare(strict_types=1);

namespace App\Http\Requests\Frame;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyOmamoriFrameRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * frameId:
     * - frames.id 존재 + soft delete 제외
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'frameId' => [
                'required',
                'integer',
                Rule::exists('frames', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'frameId.required' => 'frameId는 필수입니다.',
            'frameId.integer' => 'frameId는 정수여야 합니다.',
            'frameId.exists' => '존재하지 않는 프레임입니다.',
        ];
    }
}