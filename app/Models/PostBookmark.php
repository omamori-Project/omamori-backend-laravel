<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostBookmark extends Model
{
    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'post_id',
        'user_id',
    ];
}