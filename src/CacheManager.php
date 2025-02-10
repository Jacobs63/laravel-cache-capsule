<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use Illuminate\Cache\CacheManager as DefaultCacheManager;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class CacheManager extends DefaultCacheManager
{
    protected function createRedisDriver(array $config): Repository
    {
        $repository = new CacheRepository(
            new CacheStore(
                new RedisAdapter(
                    $this->app->make('redis')
                        ->connection($config['connection'] ?? 'default')
                        ->client(),
                ),
            ),
            $config,
        );

        if (($config['events'] ?? true) && $this->app->bound(Dispatcher::class)) {
            $repository->setEventDispatcher($this->app->make(Dispatcher::class));
        }

        return $repository;
    }
}
