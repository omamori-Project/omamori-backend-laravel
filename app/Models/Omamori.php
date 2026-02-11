<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Omamori extends Model
{
    use SoftDeletes;
    use HasFactory;
    
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'user_id',
        'title',
        'meaning',
        'status',
        'theme',
        'size_code',
        'back_message',
        'applied_fortune_color_id',
        'applied_frame_id',
        'preview_file_id',
        'view_count',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function elements()
    {
        return $this->hasMany(OmamoriElement::class)
        ->orderBy('layer')
        ->orderBy('id');
    }

    public function fortuneColor()
    {
        return $this->belongsTo(FortuneColor::class, 'applied_fortune_color_id');
    }

    public function frame()
    {
        return $this->belongsTo(Frame::class, 'applied_frame_id');
    }

    public function previewFile()
    {
        return $this->belongsTo(File::class, 'preview_file_id');
    }
}