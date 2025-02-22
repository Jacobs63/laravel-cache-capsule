<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use Coderaworks\LaravelCacheCapsule\Trait\RedisStoreTrait;
use Illuminate\Contracts\Redis\Factory;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

/**
 * @property TagAwareAdapter $adapter
 */
class RedisStore extends TaggableCacheStore
{
    use RedisStoreTrait;

    public function __construct(
        AdapterInterface $adapter,
        protected readonly Factory $redis,
        protected string $prefix,
        protected string $connection,
    )
    {
        parent::__construct($adapter);
    }

    public function connection()
    {
        return $this->redis->connection($this->connection);
    }
}
