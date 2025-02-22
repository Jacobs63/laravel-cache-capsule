<?php

declare(strict_types=1);

namespace CoderaWorks\LaravelCacheCapsule\Tests\Integration;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Testing\Concerns\InteractsWithRedis;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RedisCacheTest extends TestCase
{
    use InteractsWithRedis;

    private CacheManager $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRedis();

        $this->cacheManager = $this->app->make('cache');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->tearDownRedis();
    }

    #[DataProvider('provideCacheableData')]
    public function testCacheWorkflowWithoutTagging(mixed $value): void
    {
        $this->repository()->put('key', $value);

        $this->assertSame(
            $value,
            $this->repository()->get('key'),
        );

        $this->assertTrue($this->repository()->has('key'));

        $this->repository()->forget('key');

        $this->assertFalse($this->repository()->has('key'));

        $this->assertNull($this->repository()->get('key'));
    }

    #[DataProvider('provideCacheableData')]
    public function testCacheWorkflowWithTagging(mixed $value): void
    {
        $this->repository('tag1')->put('key', $value);

        $this->assertSame(
            $value,
            $this->repository('tag1')->get('key'),
        );

        $this->assertTrue($this->repository('tag1')->has('key'));

        $this->repository('tag1')->forget('key');

        $this->assertFalse($this->repository('tag1')->has('key'));

        $this->assertNull($this->repository('tag1')->get('key'));
    }

    #[DataProvider('provideCacheableData')]
    public function testTagsDoNotMatterWhenRetrievingCachedValue(mixed $value): void
    {
        $this->repository('tag1', 'tag2')->put('key', $value);

        $this->assertSame($value, $this->repository()->get('key'));
        $this->assertSame($value, $this->repository('tag1')->get('key'));
        $this->assertSame($value, $this->repository('tag2')->get('key'));
        $this->assertSame($value, $this->repository('tag1', 'tag2')->get('key'));
        $this->assertSame($value, $this->repository('tag2', 'tag1')->get('key'));
    }

    public function testClearingTaggedCacheClearsValue(): void
    {
        $assertEmpty = function () {
            $this->assertNull($this->repository()->get('key'));
            $this->assertNull($this->repository('tag1')->get('key'));
            $this->assertNull($this->repository('tag2')->get('key'));
            $this->assertNull($this->repository('tag1', 'tag2')->get('key'));
            $this->assertNull($this->repository('tag2', 'tag1')->get('key'));
        };

        $put = fn () => $this->repository('tag1', 'tag2')->put('key', 'value');

        $put();

        $this->repository('tag1')->forget('key');

        $assertEmpty();

        $put();

        $this->repository('tag2')->forget('key');

        $assertEmpty();

        $put();

        $this->repository('tag1', 'tag2')->forget('key');

        $assertEmpty();

        $put();

        $this->repository('tag2', 'tag1')->forget('key');

        $assertEmpty();
    }

    private function repository(string ...$tags): Repository
    {
        $repository = $this->cacheManager->driver('redis');

        if ($tags) {
            return $repository->tags($tags);
        }

        return $repository;
    }

    public static function provideCacheableData(): array
    {
        return [
            [
                0,
            ],
            [
                1
            ],
            [
                0.0,
            ],
            [
                1.0,
            ],
            [
                true,
            ],
            [
                false,
            ],
            [
                'string'
            ],
            [
                [],
            ],
            [
                [0],
            ],
            [
                [1],
            ],
            [
                [0.0],
            ],
            [
                [1.0],
            ],
            [
                [true],
            ],
            [
                [false],
            ],
            [
                ['string'],
            ],
        ];
    }
}
