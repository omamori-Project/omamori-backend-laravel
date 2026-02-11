<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Frame extends Model
{
    use SoftDeletes;
    use HasFactory;

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