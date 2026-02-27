<?php

declare(strict_types=1);

namespace RocketRouter\Contracts;

use RocketRouter\RouteItem;

interface ParameterResolverInterface
{
    /**
     * Resolve ordered parameters for a controller action from the framework's request.
     *
     * @return array<int, mixed>
     */
    public function resolve(RouteItem $route, mixed $request): array;
}
