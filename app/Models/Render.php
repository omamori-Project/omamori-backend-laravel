<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Render extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'render_code',
        'user_id',
        'omamori_id',
        'side',
        'format',
        'dpi',
        'width',
        'height',
        'store',
        'file_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function omamori()
    {
        return $this->belongsTo(Omamori::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}