<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Models\File;

class Frame extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'name',
        'frame_key',
        'preview_path',
        'is_default',
        'asset_file_id',
        'is_active',
        'meta',
    ];

    protected $appends = [
        'preview_url',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function getPreviewUrlAttribute(): ?string
    {
        if (empty($this->preview_path)) {
            return null;
        }

        return Storage::url($this->preview_path);
    }

    public function assetFile()
    {
        return $this->belongsTo(File::class, 'asset_file_id');
    }
}