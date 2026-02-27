<?php

declare(strict_types=1);

namespace RocketRouter\Tests\Fixtures\Controllers;

use RocketRouter\Attributes\ApiController;
use RocketRouter\Attributes\FromRoute;
use RocketRouter\Attributes\Route;
use RocketRouter\Attributes\RouteGet;

#[ApiController]
#[Route('/api/items')]
class OptionalParamController
{
    #[RouteGet('/{id}')]
    public function show(#[FromRoute('id')] int $id, string $format = 'json'): array
    {
        return [];
    }
}
