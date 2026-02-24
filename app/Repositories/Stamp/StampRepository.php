<?php
namespace App\Repositories\Stamp;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

class StampRepository
{
    /**
     * 스탬프 목록 조회 (필터링 / 정렬 / 페이징).
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        $dir  = 'stamps'; 
        $q    = isset($filters['q']) ? (string) $filters['q'] : null;
        $ext  = strtolower((string)($filters['ext'] ?? 'png'));
        $sort = (string)($filters['sort'] ?? 'name');
        $page = max(1, (int)($filters['page'] ?? 1));
        $size = max(1, min(100, (int)($filters['size'] ?? 24)));

        $paths = array_values(array_filter(
            $disk->files($dir),
            fn (string $path) => strtolower(pathinfo($path, PATHINFO_EXTENSION)) === $ext
        ));

        if ($q !== null && $q !== '') {
            $paths = array_values(array_filter($paths, function (string $path) use ($q): bool {
                return mb_stripos(pathinfo($path, PATHINFO_FILENAME), $q) !== false;
            }));
        }

        if ($sort === 'latest') {
            usort($paths, fn (string $a, string $b): int => $disk->lastModified($b) <=> $disk->lastModified($a));
        } else {
            usort($paths, fn (string $a, string $b): int => strcmp(
                pathinfo($a, PATHINFO_FILENAME),
                pathinfo($b, PATHINFO_FILENAME)
            ));
        }

        $total = count($paths);
        $slice = array_slice($paths, ($page - 1) * $size, $size);

        $items = array_map(fn (string $path) => [
            'asset_key' => pathinfo($path, PATHINFO_FILENAME),
            'file_name' => pathinfo($path, PATHINFO_BASENAME),
            'url' => $disk->url($path),
        ], $slice);

        return new LengthAwarePaginator(
            $items,
            $total,
            $size,
            $page,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}