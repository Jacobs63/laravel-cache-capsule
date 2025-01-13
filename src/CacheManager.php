<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use Symfony\Component\Cache\Adapter\RedisAdapter;

class CacheManager extends \Illuminate\Cache\CacheManager
{
    protected function createRedisDriver(array $config)
    {
        return $this->repository(
            new CacheStore(
                new RedisAdapter(
                    $this->app->make('redis')
                        ->connection($config['stores.redis.connection'])
                        ->client(),
                ),
                $this->getPrefix($config),
            ),
            $config,
        );
    }
}
