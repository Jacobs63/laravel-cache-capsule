<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use Carbon\Carbon;
use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Store;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class CacheStore implements Store
{
    public function __construct(
        protected readonly AdapterInterface $adapter,
        private readonly ?string $prefix,
    ) {
    }

    public function adapter(): AdapterInterface
    {
        return $this->adapter;
    }

    public function tags(string|array $tags): TaggedStore
    {
        $driver = new TaggedStore(
            new TagAwareAdapter(
                $this->adapter,
            ),
            $this->prefix,
        );

        return $driver->tags($tags);
    }

    public function get($key): mixed
    {
        return $this->adapter->getItem($key)->get();
    }

    public function forget($key): bool
    {
        return $this->adapter->deleteItem($key);
    }

    protected function parseTtl(DateInterval|DateTimeInterface|int $ttl): int
    {
        $duration = $ttl;

        $cachedNow = null;

        $now = static function () use (&$cachedNow) {
            $cachedNow ??= Carbon::now()->toImmutable();

            return $cachedNow;
        };

        if ($duration instanceof DateInterval) {
            $duration = $now()->add($duration);
        }

        if ($duration instanceof DateTimeInterface) {
            $duration = $now()->diffInSeconds($duration, false);
        }

        return (int) max($duration, 0);
    }

    public function many(array $keys)
    {
        $values = [];

        foreach ($this->adapter->getItems($keys) as $key => $value) {
            $values[$key] = $value;
        }

        return $values;
    }

    public function put($key, $value, $seconds)
    {
        $this->adapter->get(
            $key,
            function (ItemInterface $item) use ($value, $seconds) {
                $item->expiresAfter($this->parseTtl($seconds));

                return value($value);
            }
        );

        return true;
    }

    public function putMany(array $values, $seconds)
    {
        foreach ($values as $key => $value) {
            $this->put(
                $key,
                $value,
                $seconds,
            );
        }
    }

    public function increment($key, $value = 1)
    {
        $oldValue = $this->get($key);

        $this->forever($key, ++$oldValue);
    }

    public function decrement($key, $value = 1)
    {
        $oldValue = $this->get($key);

        $this->forever($key, --$oldValue);
    }

    public function forever($key, $value)
    {
        $this->put(
            $key,
            $value,
            0,
        );

        return true;
    }

    public function flush()
    {
        return $this->adapter->clear();
    }

    public function getPrefix()
    {
        return $this->prefix;
    }
}
