<?php

namespace App\Http\Requests\Omamori;

use Illuminate\Foundation\Http\FormRequest;

class IndexOmamoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:draft,published'],
            'page'   => ['nullable', 'integer', 'min:1'],
            'size'   => ['nullable', 'integer', 'min:1', 'max:50'],
            'sort'   => ['nullable', 'in:latest,oldest,title'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'status는 draft 또는 published만 가능합니다.',
            'size.max'  => '한 페이지 최대 50개까지 조회 가능합니다.',
            'sort.in'   => '정렬 기준은 latest, oldest, title만 가능합니다.',
        ];
    }
}