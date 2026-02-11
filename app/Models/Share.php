<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Share extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'omamori_id',
        'user_id',
        'token',
        'is_active',
        'view_count',
        'expires_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function omamori()
    {
        return $this->belongsTo(Omamori::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}