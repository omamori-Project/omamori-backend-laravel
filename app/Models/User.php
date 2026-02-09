<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'password_hash',
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