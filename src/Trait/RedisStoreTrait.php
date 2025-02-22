<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule\Trait;

use Illuminate\Redis\Connections\PhpRedisConnection;

trait RedisStoreTrait
{
    protected function serialize($value)
    {
        return $this->shouldBeStoredWithoutSerialization($value) ? $value : serialize($value);
    }

    /**
     * Determine if the given value should be stored as plain value.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function shouldBeStoredWithoutSerialization($value): bool
    {
        return is_numeric($value) && ! in_array($value, [INF, -INF]) && ! is_nan($value);
    }

    /**
     * Unserialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }

    /**
     * Handle connection specific considerations when a value needs to be serialized.
     *
     * @param  mixed  $value
     * @param  \Illuminate\Redis\Connections\Connection  $connection
     * @return mixed
     */
    protected function connectionAwareSerialize($value, $connection)
    {
        if ($connection instanceof PhpRedisConnection && $connection->serialized()) {
            return $value;
        }

        return $this->serialize($value);
    }

    /**
     * Handle connection specific considerations when a value needs to be unserialized.
     *
     * @param  mixed  $value
     * @param  \Illuminate\Redis\Connections\Connection  $connection
     * @return mixed
     */
    protected function connectionAwareUnserialize($value, $connection)
    {
        if ($connection instanceof PhpRedisConnection && $connection->serialized()) {
            return $value;
        }

        return $this->unserialize($value);
    }
}
