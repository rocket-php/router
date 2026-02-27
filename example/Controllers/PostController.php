<?php

declare(strict_types=1);

namespace Example\Controllers;

use RocketRouter\Attributes\ApiController;
use RocketRouter\Attributes\FromBody;
use RocketRouter\Attributes\FromRoute;
use RocketRouter\Attributes\Route;
use RocketRouter\Attributes\RouteGet;
use RocketRouter\Attributes\RoutePost;

#[ApiController]
#[Route('/api/posts')]
class PostController
{
    #[RouteGet('')]
    public function index(): array
    {
        return ['posts' => []];
    }

    #[RouteGet('/{slug}')]
    public function show(#[FromRoute('slug')] string $slug): array
    {
        return ['post' => ['slug' => $slug, 'title' => 'Hello World']];
    }

    #[RoutePost('')]
    public function store(#[FromBody] array $data): array
    {
        return ['message' => 'Post created', 'data' => $data];
    }
}
