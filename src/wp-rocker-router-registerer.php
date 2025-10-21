<?php

namespace RocketRouter;

use WP_REST_Request;

function wp_register_rocket_router_route(string $namespace, RouteItem $route): void
{
    add_action('rest_api_init', static function () use ($namespace, $route) {
        register_rest_route($namespace, $route->toWpRoute(), [
            'methods'             => $route->method,
            'callback'            => function ( WP_REST_Request $request ) use ( $route )
            {
                try
                {
                    $params = $route->resolveParameters(
                        $request->get_params(),
                        $request->get_json_params(),
                        $request->get_query_params()
                    );

                    return $route->controller->{$route->function}(...$params);
                } catch ( \Exception $e )
                {
                    return [ 'error_msg' => $e->getMessage() ];
                }
            },
            'args'                => $route['args'] ?? [],
            'permission_callback' => fn () => current_user_can( 'read' )
        ] );
    });
}