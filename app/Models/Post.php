<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'omamori_id',
        'title',
        'content',
        'omamori_snapshot',
        'tags',
        'hidden_at',
        'like_count',
        'comment_count',
        'bookmark_count',
        'view_count',
    ];

    protected $casts = [
        'omamori_snapshot' => 'array',
        'tags' => 'array',
        'hidden_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function omamori()
    {
        return $this->belongsTo(Omamori::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}