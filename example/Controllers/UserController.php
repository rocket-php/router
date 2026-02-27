<?php

declare(strict_types=1);

namespace Example\Controllers;

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
        return ['users' => [], 'filters' => $filters];
    }

    #[RouteGet('/{id}')]
    public function show(#[FromRoute('id')] int $id): array
    {
        return ['user' => ['id' => $id, 'name' => 'John']];
    }

    #[RoutePost('')]
    public function store(#[FromBody] array $data): array
    {
        return ['message' => 'User created', 'data' => $data];
    }

    #[RoutePut('/{id}')]
    public function update(#[FromRoute('id')] int $id, #[FromBody] array $data): array
    {
        return ['message' => 'User updated', 'id' => $id, 'data' => $data];
    }

    #[RouteDelete('/{id}')]
    public function destroy(#[FromRoute('id')] int $id): array
    {
        return ['message' => 'User deleted', 'id' => $id];
    }
}
