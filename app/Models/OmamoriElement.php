<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OmamoriElement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'omamori_id',
        'type',
        'layer',
        'props',
        'transform',
    ];

    protected $casts = [
        'props' => 'array',
        'transform' => 'array',
    ];

    public function omamori()
    {
        return $this->belongsTo(Omamori::class);
    }
}