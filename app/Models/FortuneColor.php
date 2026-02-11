<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FortuneColor extends Model
{
    use HasFactory;
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