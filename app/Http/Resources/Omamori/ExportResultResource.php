<?php

namespace App\Http\Resources\Omamori;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 오마모리 export 응답 리소스
 *
 * @property int $id
 * @property int|null $omamori_id
 * @property string $url
 * @property string $file_key
 * @property string|null $content_type
 * @property int|null $size_bytes
 * @property \Illuminate\Support\Carbon|string $created_at
 */
class ExportResultResource extends JsonResource
{
    /**
     * 리소스를 배열로 변환
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'file_id'      => $this->id,
            'omamori_id'   => $this->omamori_id,
            'download_url' => $this->url,       
            'file_key'     => $this->file_key,   // 다버강용
            'content_type' => $this->content_type,
            'size_bytes'   => $this->size_bytes,
            'created_at'   => $this->created_at,
        ];
    }
}