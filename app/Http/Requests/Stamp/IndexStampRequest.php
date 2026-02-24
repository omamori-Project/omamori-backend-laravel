<?php

namespace App\Http\Requests\Stamp;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 스탬프 목록 조회 요청 검증
 *
 * GET /api/v1/stamps?dir={dir}&q={q}&ext={ext}&sort={sort}&page={page}&size={size}
 */
class IndexStampRequest extends FormRequest
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
            'dir' => ['nullable', 'string', 'max:200'],
            'q' => ['nullable', 'string', 'max:50'],
            'ext' => ['nullable', 'string', 'in:png,jpg,jpeg,webp'],
            'sort' => ['nullable', 'string', 'in:name,latest'],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}