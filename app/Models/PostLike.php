<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostLike extends Model
{
    use HasFactory;
    protected $table = 'post_likes';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
    ];

    protected $casts = [
        'post_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}