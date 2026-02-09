<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FortuneColor extends Model
{
    protected $fillable = [
        'code',
        'name',
        'hex',
        'category',
        'short_meaning',
        'meaning',
        'tips',
        'is_active',
    ];

    protected $casts = [
        'tips' => 'array',
        'is_active' => 'boolean',
    ];
}