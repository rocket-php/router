<?php

declare(strict_types=1);

namespace RocketRouter\Tests\Fixtures\Controllers;

use RocketRouter\Attributes\ApiController;
use RocketRouter\Attributes\RouteGet;

#[ApiController]
class NoPrefixController
{
    #[RouteGet('/health')]
    public function health(): array
    {
        return ['status' => 'ok'];
    }
}
