<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'purpose',
        'visibility',
        'file_key',
        'url',
        'content_type',
        'size_bytes',
        'width',
        'height',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}