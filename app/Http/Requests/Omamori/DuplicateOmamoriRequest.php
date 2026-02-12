<?php

namespace App\Http\Requests\Omamori;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 오마모리 복제 요청 검증
 * - 현재는 바디 옵션이 없지만, 추후 확장(제목 변경/요소 포함 여부 등)을 대비하여 둠
 */
class DuplicateOmamoriRequest extends FormRequest
{
    /**
     * @return bool
     */
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
            // 현재 옵션 없음
        ];
    }
}