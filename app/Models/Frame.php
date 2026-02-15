<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $frame_key
 * @property string|null $preview_url
 * @property int|null $asset_file_id
 * @property bool $is_active
 * @property array<string, mixed> $meta
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $updated_at
 * @property \Carbon\CarbonInterface|null $deleted_at
 */
class Frame extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'frame_key',
        'preview_url',
        'asset_file_id',
        'is_active',
        'meta',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * 프레임 원본 파일
     *
     * @return BelongsTo
     */
    public function assetFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'asset_file_id');
    }
}