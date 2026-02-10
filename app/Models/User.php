<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;  
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;  
use Illuminate\Database\Eloquent\Factories\HasFactory;


class User extends Authenticatable 
{
    use SoftDeletes, HasApiTokens, HasFactory;

    protected $fillable = [
        'email',
        'name',
        'role',
        'is_active',
        'email_verified_at',
        'last_login_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function identities()
    {
        return $this->hasMany(UserIdentity::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function omamoris()
    {
        return $this->hasMany(Omamori::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}