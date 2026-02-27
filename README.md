# RocketRouter

A framework-agnostic PHP library for attribute-based routing. Scans controller classes at build time, generates a cache with zero runtime reflection.

## Installation

```bash
composer require asker26/rocket-router
```

## Features

- **Attribute-based routing** using PHP 8+ attributes
- **Framework-agnostic** — works with WordPress, Laravel, or any PHP framework
- **Zero runtime reflection** — all scanning happens at build time
- **OPcache-friendly** — routes are cached as a native PHP file
- **Parameter binding** via `#[FromRoute]`, `#[FromBody]`, `#[FromQuery]`

## Usage

### 1. Define Controllers

```php
<?php

use RocketRouter\Attributes\ApiController;
use RocketRouter\Attributes\Route;
use RocketRouter\Attributes\RouteGet;
use RocketRouter\Attributes\RoutePost;
use RocketRouter\Attributes\FromRoute;
use RocketRouter\Attributes\FromBody;

#[ApiController]
#[Route('/api/users')]
class UserController
{
    #[RouteGet('')]
    public function index()
    {
        return json_encode(['users' => []]);
    }

    #[RoutePost('')]
    public function store(#[FromBody] array $data)
    {
        return json_encode(['message' => 'User created']);
    }

    #[RouteGet('/{id}')]
    public function show(#[FromRoute('id')] int $id)
    {
        return json_encode(['user' => ['id' => $id]]);
    }
}
```

### 2. Generate Route Cache

Run at build/deploy time:

```bash
vendor/bin/generate-routes ./app/Controllers ./cache/routes.php
```

This scans your controllers, resolves all attributes, and writes a cache file. No reflection needed at runtime.

### 3. Load Routes at Runtime

```php
use RocketRouter\RouteCache;
use RocketRouter\RouteCollection;

$cache = new RouteCache(__DIR__ . '/cache/routes.php');
$routes = RouteCollection::fromCache($cache);

// Iterate routes
foreach ($routes as $route) {
    echo "{$route->method} {$route->route} => {$route->controller}::{$route->action}\n";
}
```

### 4. Register with a Framework

Implement `RouteRegistrarInterface` for your framework, or use the built-in WordPress adapter:

```php
use RocketRouter\RouteCache;
use RocketRouter\RouteCollection;
use RocketRouter\WordPress\WordPressRouteRegistrar;
use RocketRouter\WordPress\WordPressParameterResolver;

$cache = new RouteCache(__DIR__ . '/cache/routes.php');
$routes = RouteCollection::fromCache($cache);

$registrar = new WordPressRouteRegistrar(
    namespace: 'my-plugin/v1',
    serviceLocator: fn(string $class) => $container->get($class),
    resolver: new WordPressParameterResolver(),
);

$registrar->register($routes);
```

### 5. Available Attributes

| Attribute | Target | Description |
|---|---|---|
| `#[ApiController]` | Class | Mark a class as a scannable controller |
| `#[Route('/path')]` | Class | Define a route prefix |
| `#[RouteGet('/path')]` | Method | GET route |
| `#[RoutePost('/path')]` | Method | POST route |
| `#[RoutePut('/path')]` | Method | PUT route |
| `#[RouteDelete('/path')]` | Method | DELETE route |
| `#[FromRoute('name')]` | Parameter | Bind from route path parameter |
| `#[FromBody]` | Parameter | Bind from request JSON body |
| `#[FromQuery]` | Parameter | Bind from query string |

## Writing a Custom Adapter

Implement `RouteRegistrarInterface` and `ParameterResolverInterface`:

```php
use RocketRouter\Contracts\RouteRegistrarInterface;
use RocketRouter\Contracts\ParameterResolverInterface;
use RocketRouter\RouteCollection;
use RocketRouter\RouteItem;

class MyFrameworkRegistrar implements RouteRegistrarInterface
{
    public function register(RouteCollection $routes): void
    {
        foreach ($routes as $route) {
            // Register with your framework's router
        }
    }
}

class MyFrameworkResolver implements ParameterResolverInterface
{
    public function resolve(RouteItem $route, mixed $request): array
    {
        $args = [];
        foreach ($route->params as $param) {
            $args[] = match ($param->source) {
                'route' => /* extract from route */,
                'body'  => /* extract from body */,
                'query' => /* extract from query string */,
            };
        }
        return $args;
    }
}
```

## Requirements

- PHP 8.0 or higher

## License

MIT License. See [LICENSE](LICENSE) file for details.
