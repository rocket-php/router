<?php

declare(strict_types=1);

namespace RocketRouter\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RocketRouter\RouteItem;
use RocketRouter\RouteParam;
use RocketRouter\RouteScanner;

final class RouteScannerTest extends TestCase
{
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->fixturesDir = __DIR__ . '/Fixtures/Controllers';
    }

    public function testScanFindsAnnotatedControllers(): void
    {
        $scanner = new RouteScanner($this->fixturesDir);
        $routes = $scanner->scan();

        $this->assertNotEmpty($routes);
        $this->assertContainsOnlyInstancesOf(RouteItem::class, $routes);
    }

    public function testScanSkipsClassesWithoutApiControllerAttribute(): void
    {
        $scanner = new RouteScanner($this->fixturesDir);
        $routes = $scanner->scan();

        $controllers = array_unique(array_map(fn(RouteItem $r) => $r->controller, $routes));

        $this->assertNotContains(
            'RocketRouter\\Tests\\Fixtures\\Controllers\\NoAttributeController',
            $controllers
        );
    }

    public function testScanExtractsCorrectRoutes(): void
    {
        $scanner = new RouteScanner($this->fixturesDir);
        $routes = $scanner->scan();

        $userRoutes = array_values(array_filter(
            $routes,
            fn(RouteItem $r) => str_contains($r->controller, 'UserController')
        ));

        $this->assertCount(5, $userRoutes);

        $routeMap = [];
        foreach ($userRoutes as $r) {
            $routeMap[$r->method . ' ' . $r->route] = $r;
        }

        $this->assertArrayHasKey('GET /api/users/', $routeMap);
        $this->assertArrayHasKey('POST /api/users/', $routeMap);
        $this->assertArrayHasKey('GET /api/users/{id}', $routeMap);
        $this->assertArrayHasKey('PUT /api/users/{id}', $routeMap);
        $this->assertArrayHasKey('DELETE /api/users/{id}', $routeMap);
    }

    public function testScanExtractsParameterMetadata(): void
    {
        $scanner = new RouteScanner($this->fixturesDir);
        $routes = $scanner->scan();

        $showRoute = $this->findRoute($routes, 'GET', '/api/users/{id}');
        $this->assertNotNull($showRoute);
        $this->assertCount(1, $showRoute->params);

        $idParam = $showRoute->params[0];
        $this->assertSame('id', $idParam->name);
        $this->assertSame('int', $idParam->type);
        $this->assertSame('route', $idParam->source);
        $this->assertSame('id', $idParam->alias);
        $this->assertFalse($idParam->isOptional);
    }

    public function testScanExtractsBodyParam(): void
    {
        $scanner = new RouteScanner($this->fixturesDir);
        $routes = $scanner->scan();

        $storeRoute = $this->findRoute($routes, 'POST', '/api/users/');
        $this->assertNotNull($storeRoute);
        $this->assertCount(1, $storeRoute->params);
        $this->assertSame('body', $storeRoute->params[0]->source);
        $this->assertSame('array', $storeRoute->params[0]->type);
    }

    public function testScanExtractsQueryParam(): void
    {
        $scanner = new RouteScanner($this->fixturesDir);
        $routes = $scanner->scan();

        $indexRoute = $this->findRoute($routes, 'GET', '/api/users/');
        $this->assertNotNull($indexRoute);
        $this->assertCount(1, $indexRoute->params);
        $this->assertSame('query', $indexRoute->params[0]->source);
        $this->assertSame('array', $indexRoute->params[0]->type);
    }

    public function testScanHandlesMultipleParamsOnOneMethod(): void
    {
        $scanner = new RouteScanner($this->fixturesDir);
        $routes = $scanner->scan();

        $updateRoute = $this->findRoute($routes, 'PUT', '/api/users/{id}');
        $this->assertNotNull($updateRoute);
        $this->assertCount(2, $updateRoute->params);
        $this->assertSame('route', $updateRoute->params[0]->source);
        $this->assertSame('body', $updateRoute->params[1]->source);
    }

    public function testScanHandlesControllerWithoutRoutePrefix(): void
    {
        $scanner = new RouteScanner($this->fixturesDir);
        $routes = $scanner->scan();

        $healthRoute = $this->findRoute($routes, 'GET', '/health');
        $this->assertNotNull($healthRoute, 'Route without class-level prefix should still be found');
        $this->assertSame('health', $healthRoute->action);
    }

    public function testScanHandlesOptionalParameters(): void
    {
        $scanner = new RouteScanner($this->fixturesDir);
        $routes = $scanner->scan();

        $showRoute = $this->findRoute($routes, 'GET', '/api/items/{id}');
        $this->assertNotNull($showRoute);
        $this->assertCount(2, $showRoute->params);

        $formatParam = $showRoute->params[1];
        $this->assertSame('format', $formatParam->name);
        $this->assertTrue($formatParam->isOptional);
        $this->assertSame('json', $formatParam->default);
    }

    public function testScanThrowsOnInvalidDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $scanner = new RouteScanner('/nonexistent/directory');
        $scanner->scan();
    }

    public function testScanSetsActionName(): void
    {
        $scanner = new RouteScanner($this->fixturesDir);
        $routes = $scanner->scan();

        $indexRoute = $this->findRoute($routes, 'GET', '/api/users/');
        $this->assertSame('index', $indexRoute->action);

        $storeRoute = $this->findRoute($routes, 'POST', '/api/users/');
        $this->assertSame('store', $storeRoute->action);
    }

    /**
     * @param RouteItem[] $routes
     */
    private function findRoute(array $routes, string $method, string $route): ?RouteItem
    {
        foreach ($routes as $r) {
            if ($r->method === $method && $r->route === $route) {
                return $r;
            }
        }
        return null;
    }
}
