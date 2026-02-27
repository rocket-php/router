<?php

declare(strict_types=1);

namespace RocketRouter;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, RouteItem>
 */
final class RouteCollection implements IteratorAggregate, Countable
{
    /**
     * @param RouteItem[] $routes
     */
    public function __construct(
        private readonly array $routes,
    ) {
    }

    public static function fromCache(RouteCache $cache): self
    {
        return new self($cache->read());
    }

    /**
     * @return RouteItem[]
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * @return RouteItem[]
     */
    public function byMethod(string $method): array
    {
        return array_values(
            array_filter($this->routes, fn(RouteItem $r) => $r->method === $method)
        );
    }

    /**
     * @return ArrayIterator<int, RouteItem>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->routes);
    }

    public function count(): int
    {
        return count($this->routes);
    }
}
