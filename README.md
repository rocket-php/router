# RocketRouter

A PHP package for attribute-based routing with automatic route generation from controller classes.

## Installation

Install via Composer:

```bash
composer require rocket-php/router
```

## Features

- **Attribute-based routing** using PHP 8+ attributes
- **Automatic route discovery** from controller classes
- **Route cache generation** for improved performance
- **Console command** for easy route generation
- **Support for all HTTP methods** (GET, POST, PUT, DELETE, PATCH)

## Usage

### 1. Define Controllers with Attributes

Create controller classes using the provided attributes:

```php
<?php

use RocketRouter\Attributes\ApiController;
use RocketRouter\Attributes\Route;
use RocketRouter\Attributes\RouteGet;
use RocketRouter\Attributes\RoutePost;

#[ApiController]
#[Route('/api/users')]
class UserController
{
    #[RouteGet('')]
    public function index()
    {
        // GET /api/users
        return json_encode(['users' => []]);
    }

    #[RoutePost('')]
    public function store()
    {
        // POST /api/users
        return json_encode(['message' => 'User created']);
    }

    #[RouteGet('/{id}')]
    public function show($id)
    {
        // GET /api/users/123
        return json_encode(['user' => ['id' => $id]]);
    }
}
```

### 2. Generate Routes

Use the console command to scan your controllers and generate a route cache:

```bash
# Generate routes from your controllers directory
vendor/bin/generate-routes ./app/Controllers ./cache/routes.php
```

### 3. Available Attributes

- `#[ApiController]` - Mark a class as an API controller
- `#[Route('/path')]` - Define base route for the controller
- `#[RouteGet('/path')]` - Define GET route for a method
- `#[RoutePost('/path')]` - Define POST route for a method
- `#[RouteDelete('/path')]` - Define DELETE route for a method
- `#[RoutePut('/path')]` - Define PUT route for a method (if implemented)
- `#[RoutePatch('/path')]` - Define PATCH route for a method (if implemented)

## Requirements

- PHP 8.0 or higher

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
