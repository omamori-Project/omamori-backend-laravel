<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserIdentity extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'email',
        'password_hash',
        'profile',
        'linked_at',
        'last_used_at',
        'revoked_at',
    ];

    protected $casts = [
        'profile' => 'array',
        'linked_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}