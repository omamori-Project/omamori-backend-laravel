<?php

namespace App\Http\Requests\Omamori;

use Illuminate\Foundation\Http\FormRequest;

class ExportOmamoriRequest extends FormRequest
{
    /**
     * 요청 권한
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 요청 바디 검증 규칙
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * export 포맷
             * - 현재는 png만 지원
             */
            'format' => ['sometimes', 'string', 'in:png'],
        ];
    }

    /**
     * 검증된 옵션을 ExportService로 넘기기 위한 헬퍼
     *
     * @return array{format?:string}
     */
    public function options(): array
    {
        /** @var array{format?:string} $validated */
        $validated = $this->validated();

        return $validated;
    }
}