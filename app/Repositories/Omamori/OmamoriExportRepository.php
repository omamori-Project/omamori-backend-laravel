<?php

namespace App\Repositories\Omamori;

use App\Models\File;

/**
 * Class OmamoriExportRepository
 *
 * Export 결과(파일 메타)를 DB에 저장/조회하는 레이어
 * @package App\Repositories\Omamori
 */
class OmamoriExportRepository
{
    /**
     * Export 결과 파일 메타 레코드를 생성
     *
     * @param array{
     *   user_id:int,
     *   omamori_id:int|null,
     *   purpose:string,
     *   visibility:string,
     *   file_key:string,
     *   url:string,
     *   content_type?:string|null,
     *   size_bytes?:int|null,
     *   width?:int|null,
     *   height?:int|null
     * } $data
     *
     * @return File
     */
    public function create(array $data): File
    {
        return File::create($data);
    }
}