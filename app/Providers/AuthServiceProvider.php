<?php

namespace App\Providers;

use App\Models\Omamori;
use App\Models\Share;
use App\Models\Post;
use App\Models\Comment;
use App\Policies\OmamoriPolicy;
use App\Policies\SharePolicy;
use App\Policies\PostPolicy;
use App\Policies\CommentPolicy;
use App\Models\FortuneColor;
use App\Policies\FortuneColorPolicy;
use App\Models\Frame;
use App\Policies\FramePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Omamori::class => OmamoriPolicy::class,
        Share::class   => SharePolicy::class,
        Post::class => PostPolicy::class,
        Comment::class => CommentPolicy::class,
        FortuneColor::class => FortuneColorPolicy::class,
        Frame::class => FramePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}