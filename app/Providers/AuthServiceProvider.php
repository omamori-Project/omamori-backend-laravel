<?php

namespace App\Providers;

use App\Models\Omamori;
use App\Models\Share;
use App\Policies\OmamoriPolicy;
use App\Policies\SharePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Omamori::class => OmamoriPolicy::class,
        Share::class   => SharePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}