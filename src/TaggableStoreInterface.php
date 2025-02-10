<?php

declare(strict_types=1);

namespace Coderaworks\LaravelCacheCapsule;

interface TaggableStoreInterface
{
    public function tags($tags): static;
}
