<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use Illuminate\Cache\CacheManager as DefaultCacheManager;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class CacheManager extends DefaultCacheManager
{
    protected function createRedisDriver(array $config): Repository
    {
        $redis = $this->app->make('redis')
            ->connection($config['connection'] ?? 'default')
            ->client();

        $adapter = new TagAwareAdapter(
            new RedisAdapter($redis),
        );

        $store = new TaggableCacheStore($adapter);

        $repository = new CacheRepository($store, $config);

        if (($config['events'] ?? true) && $this->app->bound(Dispatcher::class)) {
            $repository->setEventDispatcher($this->app->make(Dispatcher::class));
        }

        return $repository;
    }
}
