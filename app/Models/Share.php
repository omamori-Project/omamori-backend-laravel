<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Share extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'omamori_id',
        'share_code',
        'is_public',
        'view_count',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function omamori()
    {
        return $this->belongsTo(Omamori::class);
    }
}