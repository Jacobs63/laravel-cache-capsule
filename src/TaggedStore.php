<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use Illuminate\Contracts\Cache\Store;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @property TagAwareAdapter $adapter
 */
class TaggedStore extends CacheStore implements TaggableStoreInterface
{
    protected array $tags = [];

    public function __construct(
        protected readonly Store $store,
        TagAwareAdapter $adapter,
    )
    {
        parent::__construct($adapter);
    }

    public function tags($tags): static
    {
        $this->tags = is_array($tags) ? $tags : [$tags];

        return $this;
    }

    public function put(
        $key,
        $value,
        $seconds,
    ): bool {
        $this->adapter->get(
            $key,
            function (ItemInterface $item) use ($value, $seconds) {
                $item->tag($this->tags);

                $item->expiresAfter($this->parseTtl($seconds));

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
}
