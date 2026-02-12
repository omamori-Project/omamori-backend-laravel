<?php

namespace App\Services\Omamori;

use App\Models\File;
use App\Models\Omamori;
use App\Repositories\Omamori\OmamoriExportRepository;
use App\Repositories\Omamori\OmamoriDuplicateRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Filesystem\FilesystemAdapter;

/**
 *
 * 오마모리 내보내기(Export) 유스케이스 처리
 * - 현재 단계에서는 "다운로드 URL 반환"을 위해 File 메타를 생성
 * - 실제 렌더/파일 생성(예: PNG 생성)은 추후 별도 Renderer/Job로 확장
 *
 * 정책:
 * - 본인 소유 오마모리만 export 가능
 * - export 결과는 files.purpose = 'render_output'
 * - export 결과는 기본 private 권장(외부 공유는 shares로 처리)
 */
class OmamoriExportService
{
    public function __construct(
        private readonly OmamoriExportRepository $exportRepository,
        private readonly OmamoriDuplicateRepository $omamoriRepository,
    ) {}

    /**
     * 오마모리를 export하고 다운로드 URL을 제공하기 위한 File 메타를 생성한다.
     *
     * @param int $userId 로그인 유저 ID
     * @param int $omamoriId 대상 오마모리 ID
     * @param array{
     *   format?:string
     * } $options
     *
     * @return File 생성된 파일 메타
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403/404 등
     */
    public function export(int $userId, int $omamoriId, array $options = []): File
    {
        /** @var Omamori $omamori */
        $omamori = $this->omamoriRepository->findOmamoriOrFail($omamoriId);

        // 권한 정책: 소유자만 export 가능
        if ((int) $omamori->user_id !== (int) $userId) {
            abort(403, 'Forbidden');
        }

        // 현재 지원 포맷(확장 가능): png
        $format = $options['format'] ?? 'png';

        // files.file_key는 unique이므로 UUID로 충돌 방지
        $fileKey = sprintf(
            'exports/omamoris/%d/%s.%s',
            $omamoriId,
            Str::uuid()->toString(),
            $format
        );

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('filesystems.default', 'public'));

        // url은 Storage disk 정책에 따라 결정
        // 배포 시 S3로 전환 시 S3 URL 반환
        $url = $disk->url($fileKey);

        // File 메타 생성 
        // 실제 파일 생성은 별도 Job/Renderer에서 처리 예정
        return $this->exportRepository->create([
            'user_id'      => $userId,
            'omamori_id'   => $omamoriId,
            'purpose'      => 'render_output',
            'visibility'   => 'private',
            'file_key'     => $fileKey,
            'url'          => $url,
            'content_type' => $format === 'png' ? 'image/png' : null,
            'size_bytes'   => null,
            'width'        => null,
            'height'       => null,
        ]);
    }
}