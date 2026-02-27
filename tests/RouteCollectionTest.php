<?php

declare(strict_types=1);

namespace RocketRouter\Tests;

use PHPUnit\Framework\TestCase;
use RocketRouter\RouteCache;
use RocketRouter\RouteCollection;
use RocketRouter\RouteItem;

final class RouteCollectionTest extends TestCase
{
    public function testAllReturnsAllRoutes(): void
    {
        $routes = $this->createRoutes();
        $collection = new RouteCollection($routes);

        $this->assertSame($routes, $collection->all());
    }

    public function testCountReturnsCorrectCount(): void
    {
        $collection = new RouteCollection($this->createRoutes());

        $this->assertCount(4, $collection);
    }

    public function testByMethodFiltersCorrectly(): void
    {
        $collection = new RouteCollection($this->createRoutes());

        $getRoutes = $collection->byMethod('GET');
        $this->assertCount(2, $getRoutes);
        foreach ($getRoutes as $route) {
            $this->assertSame('GET', $route->method);
        }

        $postRoutes = $collection->byMethod('POST');
        $this->assertCount(1, $postRoutes);

        $deleteRoutes = $collection->byMethod('DELETE');
        $this->assertCount(1, $deleteRoutes);

        $patchRoutes = $collection->byMethod('PATCH');
        $this->assertEmpty($patchRoutes);
    }

    public function testIsIterable(): void
    {
        $routes = $this->createRoutes();
        $collection = new RouteCollection($routes);

        $iterated = [];
        foreach ($collection as $route) {
            $iterated[] = $route;
        }

        $this->assertCount(4, $iterated);
        $this->assertSame($routes[0], $iterated[0]);
    }

    public function testFromCache(): void
    {
        $cacheFile = sys_get_temp_dir() . '/rocket-router-collection-test-' . uniqid() . '/routes.php';

        try {
            $cache = new RouteCache($cacheFile);
            $cache->write($this->createRoutes());

            $collection = RouteCollection::fromCache($cache);

            $this->assertCount(4, $collection);
            $this->assertInstanceOf(RouteItem::class, $collection->all()[0]);
        } finally {
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
            $dir = dirname($cacheFile);
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    public function testEmptyCollection(): void
    {
        $collection = new RouteCollection([]);

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection->all());
        $this->assertEmpty($collection->byMethod('GET'));
    }

    /**
     * @return RouteItem[]
     */
    private function createRoutes(): array
    {
        return [
            new RouteItem('/api/users/', 'GET', 'UserController', 'index', []),
            new RouteItem('/api/users/{id}', 'GET', 'UserController', 'show', []),
            new RouteItem('/api/users/', 'POST', 'UserController', 'store', []),
            new RouteItem('/api/users/{id}', 'DELETE', 'UserController', 'destroy', []),
        ];
    }
}
