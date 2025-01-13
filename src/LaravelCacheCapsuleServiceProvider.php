<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class LaravelCacheCapsuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend(
            Factory::class,
            fn (Factory $factory, Application $app) => new CacheManager($app),
        );
    }
}
