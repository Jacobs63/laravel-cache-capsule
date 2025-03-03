<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use BadMethodCallException;
use Closure;
use Illuminate\Cache\Repository as BaseRepository;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Events\Dispatcher;

class CacheRepository implements Repository
{
    protected ?Dispatcher $events = null;

    private BaseRepository $base;

    public function __construct(
        protected readonly CacheStore $store,
        protected readonly array $config = [],
    )
    {
        $this->base = new BaseRepository($this->store, $this->config);
    }

    public function supportsTags(): bool
    {
        return $this->store instanceof TaggableStoreInterface;
    }

    public function get($key, $default = null): mixed
    {
        return $this->store->get($key) ?? $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->base->set($key, $value, $ttl);
    }

    public function delete($key): bool
    {
        return $this->base->delete($key);
    }

    public function clear(): bool
    {
        return $this->base->clear();
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return $this->base->getMultiple($keys, $default);
    }

    public function setMultiple($values, $ttl = null): bool
    {
        return $this->base->setMultiple($values, $ttl);
    }

    public function deleteMultiple($keys): bool
    {
        return $this->base->deleteMultiple($keys);
    }

    public function has($key): bool
    {
        return $this->base->has($key);
    }

    public function pull($key, $default = null)
    {
        return $this->base->pull($key, $default);
    }

    public function put($key, $value, $ttl = null): bool
    {
        return $this->base->put($key, $value, $ttl);
    }

    public function add($key, $value, $ttl = null)
    {
        return $this->base->add($key, $value, $ttl);
    }

    public function increment($key, $value = 1): bool|int
    {
        return $this->base->increment($key, $value);
    }

    public function decrement($key, $value = 1): bool|int
    {
        return $this->base->decrement($key, $value);
    }

    public function forever($key, $value): bool
    {
        return $this->base->forever($key, $value);
    }

    public function remember($key, $ttl, Closure $callback): mixed
    {
        return $this->base->remember($key, $ttl, $callback);
    }

    public function sear($key, Closure $callback): mixed
    {
        return $this->base->sear($key, $callback);
    }

    public function rememberForever($key, Closure $callback): mixed
    {
        return $this->base->rememberForever($key, $callback);
    }

    public function forget($key): bool
    {
        return $this->base->forget($key);
    }

    public function getStore(): Store
    {
        return $this->store;
    }

    /**
     * @param string|string[] ...$names
     * @return Repository
     */
    public function tags($names): Repository
    {
        if (! $this->supportsTags()) {
            throw new BadMethodCallException('This cache driver does not support tagging.');
        }

        $this->store->tags(is_array($names) ? $names : func_get_args());

        return $this;
    }

    public function setEventDispatcher(Dispatcher $events): void
    {
        $this->events = $events;
    }
}
