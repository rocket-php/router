<?php

declare(strict_types=1);

namespace RocketRouter;

final class RouteItem
{
    public function __construct(
        public readonly string $route,
        public readonly string $method,
        public readonly string $controller,
        public readonly string $action,
        /** @var RouteParam[] */
        public readonly array $params,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function __set_state(array $data): self
    {
        return new self(
            route: $data['route'],
            method: $data['method'],
            controller: $data['controller'],
            action: $data['action'],
            params: $data['params'],
        );
    }
}
