<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

class TaggedRepository extends CacheRepository
{
    public function __construct(TaggedStore $store)
    {
        parent::__construct($store);
    }
}
