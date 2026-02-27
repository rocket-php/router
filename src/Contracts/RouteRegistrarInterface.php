<?php

declare(strict_types=1);

namespace RocketRouter\Contracts;

use RocketRouter\RouteCollection;

interface RouteRegistrarInterface
{
    public function register(RouteCollection $routes): void;
}
