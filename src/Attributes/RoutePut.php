<?php

declare(strict_types=1);

namespace RocketRouter\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class RoutePut extends HttpMethod
{
    public function __construct(
        public string $route = '',
    )
    {
        parent::__construct( 'PUT', $route );
    }
}