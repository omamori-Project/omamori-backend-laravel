<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use SoftDeletes;
    use HasFactory;

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