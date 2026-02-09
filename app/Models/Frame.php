<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Frame extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'frame_key',
        'preview_url',
        'asset_file_id',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    public function assetFile()
    {
        return $this->belongsTo(File::class, 'asset_file_id');
    }
}