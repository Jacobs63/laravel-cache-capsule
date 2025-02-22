<?php

declare(strict_types=1);

namespace CoderaWorks\LaravelCacheCapsule\Tests\Integration;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;
use Orchestra\Testbench\TestCase;

class RedisCacheTest extends TestCase
{
    private CacheManager $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheManager = $this->app->make('cache');
    }

    public function testCacheWorkflowWithoutTagging(): void
    {
        $this->repository()->put('key', 10);

        $this->assertSame(
            10,
            $this->repository()->get('key'),
        );

        $this->assertTrue($this->repository()->has('key'));

        $this->repository()->forget('key');

        $this->assertFalse($this->repository()->has('key'));

        $this->assertNull($this->repository()->get('key'));
    }

    public function testCacheWorkflowWithTagging(): void
    {
        $this->repository(['tag1'])->put('key', 10);

        $this->assertSame(
            10,
            $this->repository(['tag1'])->get('key'),
        );

        $this->assertTrue($this->repository(['tag1'])->has('key'));

        $this->repository(['tag1'])->forget('key');

        $this->assertFalse($this->repository(['tag1'])->has('key'));

        $this->assertNull($this->repository(['tag1'])->get('key'));
    }

    private function repository(array $tags = []): Repository
    {
        $repository = $this->cacheManager->driver('redis');

        if ($tags) {
            return $repository->tags($tags);
        }

        return $repository;
    }
}
