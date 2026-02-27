<?php

declare(strict_types=1);

namespace Example\Controllers;

use RocketRouter\Attributes\ApiController;
use RocketRouter\Attributes\RouteGet;

#[ApiController]
class HealthController
{
    #[RouteGet('/health')]
    public function check(): array
    {
        return ['status' => 'ok', 'timestamp' => time()];
    }
}
