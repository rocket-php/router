<?php

declare(strict_types=1);

namespace RocketRouter\Tests;

use PHPUnit\Framework\TestCase;
use RocketRouter\RouteCache;
use RocketRouter\RouteItem;
use RocketRouter\RouteParam;
use RuntimeException;

final class RouteCacheTest extends TestCase
{
    private string $cacheDir;
    private string $cacheFile;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/rocket-router-test-' . uniqid();
        $this->cacheFile = $this->cacheDir . '/routes.php';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
        if (is_dir($this->cacheDir)) {
            rmdir($this->cacheDir);
        }
    }

    public function testWriteAndRead(): void
    {
        $routes = $this->createSampleRoutes();

        $cache = new RouteCache($this->cacheFile);
        $cache->write($routes);

        $this->assertFileExists($this->cacheFile);

        $loaded = $cache->read();

        $this->assertCount(2, $loaded);
        $this->assertInstanceOf(RouteItem::class, $loaded[0]);
        $this->assertInstanceOf(RouteItem::class, $loaded[1]);
    }

    public function testRoundTripPreservesData(): void
    {
        $routes = $this->createSampleRoutes();

        $cache = new RouteCache($this->cacheFile);
        $cache->write($routes);
        $loaded = $cache->read();

        $this->assertSame('/api/users/', $loaded[0]->route);
        $this->assertSame('GET', $loaded[0]->method);
        $this->assertSame('App\\Controllers\\UserController', $loaded[0]->controller);
        $this->assertSame('index', $loaded[0]->action);

        $this->assertSame('/api/users/{id}', $loaded[1]->route);
        $this->assertSame('GET', $loaded[1]->method);
        $this->assertSame('show', $loaded[1]->action);
    }

    public function testRoundTripPreservesParams(): void
    {
        $routes = $this->createSampleRoutes();

        $cache = new RouteCache($this->cacheFile);
        $cache->write($routes);
        $loaded = $cache->read();

        $this->assertCount(1, $loaded[1]->params);

        $param = $loaded[1]->params[0];
        $this->assertInstanceOf(RouteParam::class, $param);
        $this->assertSame('id', $param->name);
        $this->assertSame('int', $param->type);
        $this->assertSame('route', $param->source);
        $this->assertSame('id', $param->alias);
        $this->assertFalse($param->isOptional);
    }

    public function testExistsReturnsFalseWhenNoCacheFile(): void
    {
        $cache = new RouteCache($this->cacheFile);
        $this->assertFalse($cache->exists());
    }

    public function testExistsReturnsTrueAfterWrite(): void
    {
        $cache = new RouteCache($this->cacheFile);
        $cache->write([]);
        $this->assertTrue($cache->exists());
    }

    public function testReadThrowsWhenFileDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);

        $cache = new RouteCache('/nonexistent/path/routes.php');
        $cache->read();
    }

    public function testWriteCreatesDirectoryIfMissing(): void
    {
        $this->assertDirectoryDoesNotExist($this->cacheDir);

        $cache = new RouteCache($this->cacheFile);
        $cache->write([]);

        $this->assertDirectoryExists($this->cacheDir);
        $this->assertFileExists($this->cacheFile);
    }

    public function testWriteEmptyArray(): void
    {
        $cache = new RouteCache($this->cacheFile);
        $cache->write([]);

        $loaded = $cache->read();
        $this->assertIsArray($loaded);
        $this->assertEmpty($loaded);
    }

    /**
     * @return RouteItem[]
     */
    private function createSampleRoutes(): array
    {
        return [
            new RouteItem(
                route: '/api/users/',
                method: 'GET',
                controller: 'App\\Controllers\\UserController',
                action: 'index',
                params: [],
            ),
            new RouteItem(
                route: '/api/users/{id}',
                method: 'GET',
                controller: 'App\\Controllers\\UserController',
                action: 'show',
                params: [
                    new RouteParam(
                        name: 'id',
                        type: 'int',
                        source: 'route',
                        alias: 'id',
                        isOptional: false,
                        default: null,
                    ),
                ],
            ),
        ];
    }
}
