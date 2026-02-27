#!/usr/bin/env php
<?php

/**
 * Runtime entry point — loads routes from cache (zero reflection)
 * and dispatches a simulated request.
 *
 * In a real app this would be your framework's bootstrap.
 * Here we just match the route manually to keep the example minimal.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use RocketRouter\RouteCache;
use RocketRouter\RouteCollection;
use RocketRouter\RouteItem;
use RocketRouter\RouteParam;

// ── 1. Load routes from cache (no reflection) ──────────────────────

$cache  = new RouteCache(__DIR__ . '/cache/routes.php');
$routes = RouteCollection::fromCache($cache);

echo "Loaded {$routes->count()} route(s) from cache.\n\n";

// ── 2. Print the route table ────────────────────────────────────────

echo "Route table:\n";
echo str_repeat('─', 70) . "\n";
printf("  %-8s %-30s %s\n", 'METHOD', 'ROUTE', 'HANDLER');
echo str_repeat('─', 70) . "\n";

foreach ($routes as $route) {
    printf("  %-8s %-30s %s::%s\n",
        $route->method,
        $route->route,
        $route->controller,
        $route->action,
    );
}

echo str_repeat('─', 70) . "\n\n";

// ── 3. Simulate a request ───────────────────────────────────────────

$simulatedMethod = 'GET';
$simulatedPath   = '/api/users/42';

echo "Simulating: {$simulatedMethod} {$simulatedPath}\n\n";

$matched = matchRoute($routes, $simulatedMethod, $simulatedPath);

if ($matched === null) {
    echo "  404 — no matching route.\n";
    exit(1);
}

['route' => $routeItem, 'params' => $routeParams] = $matched;

echo "  Matched: {$routeItem->controller}::{$routeItem->action}\n";

// Resolve parameters from the matched route segments
$args = resolveParams($routeItem, $routeParams, [], []);
echo "  Args:    " . json_encode($args, JSON_PRETTY_PRINT) . "\n\n";

// Instantiate controller and call the action
$controller = new ($routeItem->controller)();
$result     = $controller->{$routeItem->action}(...$args);

echo "  Result:  " . json_encode($result, JSON_PRETTY_PRINT) . "\n";

// ── Helpers ─────────────────────────────────────────────────────────

/**
 * Minimal route matcher — converts {param} to a regex, tests the path.
 * A real framework would do this better.
 */
function matchRoute(RouteCollection $routes, string $method, string $path): ?array
{
    foreach ($routes as $route) {
        if ($route->method !== $method) {
            continue;
        }

        $pattern = preg_replace('#\{(\w+)}#', '(?P<$1>[^/]+)', $route->route);
        $pattern = '#^' . rtrim($pattern, '/') . '$#';

        if (preg_match($pattern, rtrim($path, '/'), $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return ['route' => $route, 'params' => $params];
        }
    }

    return null;
}

/**
 * Resolve controller arguments from RouteParam metadata.
 * This is what a ParameterResolverInterface implementation does —
 * here we inline it to keep the example self-contained.
 */
function resolveParams(RouteItem $route, array $routeParams, array $bodyParams, array $queryParams): array
{
    $args = [];

    foreach ($route->params as $param) {
        $key = $param->alias ?? $param->name;

        $args[] = match ($param->source) {
            'route' => castParam($routeParams[$key] ?? ($param->isOptional ? $param->default : null), $param->type),
            'body'  => $bodyParams,
            'query' => $queryParams,
        };
    }

    return $args;
}

function castParam(mixed $value, string $type): mixed
{
    return match ($type) {
        'int'    => (int) $value,
        'float'  => (float) $value,
        'bool'   => (bool) $value,
        default  => $value,
    };
}
