<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use Carbon\Carbon;
use DateInterval;
use DateTimeInterface;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class CacheStore implements Store
{
    protected ?Dispatcher $events = null;

    /**
     * The default number of seconds to store items.
     *
     * @var int|null
     */
    protected $default = 3600;

    public function __construct(
        protected readonly AdapterInterface $adapter,
    ) {
    }

    public function adapter(): AdapterInterface
    {
        return $this->adapter;
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

    public function many(array $keys): array
    {
        return iterator_to_array($this->adapter->getItems($keys));
    }

    public function put($key, $value, $seconds): bool
    {
        $cachedValue = $this->adapter->get(
            $key,
            function (ItemInterface $item) use ($value, $seconds) {
                if ($seconds) {
                    $item->expiresAfter($this->parseTtl($seconds));
                }

                return value($value);
            }
        );

        return $cachedValue !== null;
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

    public function increment($key, $value = 1): bool
    {
        $oldValue = $this->get($key);

        return $this->forever($key, ++$oldValue);
    }

    public function decrement($key, $value = 1): bool
    {
        $oldValue = $this->get($key);

        return $this->forever($key, --$oldValue);
    }

    public function forever($key, $value): bool
    {
        return $this->put(
            $key,
            $value,
            0,
        );
    }

    public function flush(): bool
    {
        return $this->adapter->clear();
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function setEventDispatcher(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * Set the default cache time in seconds.
     *
     * @param  int|null  $seconds
     * @return $this
     */
    public function setDefaultCacheTime($seconds)
    {
        $this->default = $seconds;

        return $this;
    }
}
