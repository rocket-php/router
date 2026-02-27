<?php

declare(strict_types=1);

namespace RocketRouter\WordPress;

use Closure;
use RocketRouter\Contracts\RouteRegistrarInterface;
use RocketRouter\RouteCollection;
use RocketRouter\RouteItem;
use WP_REST_Request;

final class WordPressRouteRegistrar implements RouteRegistrarInterface
{
    public function __construct(
        private readonly string $namespace,
        private readonly Closure $serviceLocator,
        private readonly WordPressParameterResolver $resolver,
    ) {
    }

    public function register(RouteCollection $routes): void
    {
        add_action('rest_api_init', function () use ($routes) {
            foreach ($routes as $route) {
                $this->registerRoute($route);
            }
        });
    }

    private function registerRoute(RouteItem $route): void
    {
        $wpPattern = preg_replace('#\{(\w+)}#', '(?P<$1>[^/]+)', $route->route);

        register_rest_route($this->namespace, $wpPattern, [
            'methods' => $route->method,
            'callback' => function (WP_REST_Request $request) use ($route) {
                $controller = ($this->serviceLocator)($route->controller);
                $params = $this->resolver->resolve($route, $request);
                return $controller->{$route->action}(...$params);
            },
            'permission_callback' => fn() => current_user_can('read'),
        ]);
    }
}
