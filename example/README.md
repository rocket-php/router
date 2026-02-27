# RocketRouter Example

A minimal project that shows the full lifecycle: define controllers, build the route cache, and dispatch a request at runtime with zero reflection.

## Project structure

```
example/
  Controllers/
    UserController.php       # CRUD routes for /api/users
    PostController.php       # Routes for /api/posts
    HealthController.php     # Single GET /health (no route prefix)
  cache/
    routes.php               # Generated — do not edit
  build.php                  # Build step: scans controllers, writes cache
  index.php                  # Runtime: loads cache, matches a route, calls the controller
```

## How it works

### 1. Controllers use attributes

```php
#[ApiController]
#[Route('/api/users')]
class UserController
{
    #[RouteGet('/{id}')]
    public function show(#[FromRoute('id')] int $id): array
    {
        return ['user' => ['id' => $id]];
    }
}
```

- `#[ApiController]` marks the class as scannable.
- `#[Route('/api/users')]` sets the prefix for all methods in the class.
- `#[RouteGet('/{id}')]` registers a GET endpoint.
- `#[FromRoute('id')]` binds the `{id}` path segment to the `$id` parameter.

### 2. Build step (reflection happens here, once)

```bash
php example/build.php
```

This runs `RouteScanner` against `example/Controllers/`, resolves every attribute and parameter type via reflection, and writes the result to `example/cache/routes.php` using `var_export`. The cache file is plain PHP — no serialization tricks, fully OPcache-friendly.

### 3. Runtime (zero reflection)

```bash
php example/index.php
```

This loads routes from the cache file (a single `require`), prints the route table, then simulates a `GET /api/users/42` request:

1. Iterates `RouteCollection` to find a matching route.
2. Reads `RouteParam` metadata to resolve controller arguments from the path.
3. Instantiates the controller and calls the action.

No reflection, no attribute scanning — just reading pre-computed data.

## Autoloading

The example controllers use the `Example\` namespace. This is registered in the project's `composer.json` under `autoload-dev`:

```json
"autoload-dev": {
    "psr-4": {
        "Example\\": "example/"
    }
}
```

After cloning, run:

```bash
composer install
```

## Output

```
$ php example/build.php
Build complete — 9 route(s) cached.

  GET   /api/users/     => Example\Controllers\UserController::index
  GET   /api/users/{id} => Example\Controllers\UserController::show
  POST  /api/users/     => Example\Controllers\UserController::store
  PUT   /api/users/{id} => Example\Controllers\UserController::update
  DELETE /api/users/{id} => Example\Controllers\UserController::destroy
  GET   /api/posts/     => Example\Controllers\PostController::index
  GET   /api/posts/{slug} => Example\Controllers\PostController::show
  POST  /api/posts/     => Example\Controllers\PostController::store
  GET   /health         => Example\Controllers\HealthController::check

$ php example/index.php
Loaded 9 route(s) from cache.

Simulating: GET /api/users/42

  Matched: Example\Controllers\UserController::show
  Args:    [42]
  Result:  {"user":{"id":42,"name":"John"}}
```
