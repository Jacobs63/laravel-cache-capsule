<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @property TagAwareAdapter $adapter
 */
class TaggedStore extends CacheStore
{
    protected array $tags = [];

    public function __construct(
        TagAwareAdapter $adapter,
        string $prefix,
    )
    {
        parent::__construct($adapter, $prefix);
    }

    public function tags(string|array $tags): TaggedStore
    {
        $this->tags = is_array($tags) ? $tags : [$tags];

        return $this;
    }

    public function put(
        $key,
        $value,
        $seconds,
    ) {
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
    ) {
        $this->adapter->get(
            $key,
            static function (ItemInterface $item) use ($value) {
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
