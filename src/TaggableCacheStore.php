<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @property TagAwareAdapter $adapter
 */
class TaggableCacheStore extends CacheStore implements TaggableStoreInterface
{
    protected array $tags = [];

    public function put(
        $key,
        $value,
        $seconds,
    ): bool {
        $this->adapter->get(
            $key,
            function (ItemInterface $item) use ($value, $seconds) {
                $item->tag($this->tags);

                if ($seconds) {
                    $item->expiresAfter($this->parseTtl($seconds));
                }

                return value($value);
            }
        );

        return true;
    }

    public function forever(
        $key,
        $value,
    ): bool {
        $this->adapter->get(
            $key,
            function (ItemInterface $item) use ($value) {
                $item->tag($this->tags);

                return value($value);
            }
        );

        return true;
    }

    public function flush(): bool
    {
        return $this->adapter->invalidateTags($this->tags);
    }

    public function tags($tags): static
    {
        $this->tags = $tags;

        return $this;
    }
}
