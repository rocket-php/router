<?php

declare(strict_types=1);

namespace RocketRouter\Tests\Fixtures\Controllers;

use RocketRouter\Attributes\ApiController;
use RocketRouter\Attributes\FromBody;
use RocketRouter\Attributes\FromQuery;
use RocketRouter\Attributes\FromRoute;
use RocketRouter\Attributes\Route;
use RocketRouter\Attributes\RouteDelete;
use RocketRouter\Attributes\RouteGet;
use RocketRouter\Attributes\RoutePost;
use RocketRouter\Attributes\RoutePut;

#[ApiController]
#[Route('/api/users')]
class UserController
{
    #[RouteGet('')]
    public function index(#[FromQuery] array $filters): array
    {
        return [];
    }

    #[RoutePost('')]
    public function store(#[FromBody] array $data): array
    {
        return [];
    }

    #[RouteGet('/{id}')]
    public function show(#[FromRoute('id')] int $id): array
    {
        return [];
    }

    #[RoutePut('/{id}')]
    public function update(#[FromRoute('id')] int $id, #[FromBody] array $data): array
    {
        return [];
    }

    #[RouteDelete('/{id}')]
    public function destroy(#[FromRoute('id')] int $id): array
    {
        return [];
    }
}
