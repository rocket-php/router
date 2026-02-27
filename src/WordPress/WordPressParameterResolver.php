<?php

declare(strict_types=1);

namespace RocketRouter\WordPress;

use RocketRouter\Contracts\ParameterResolverInterface;
use RocketRouter\RouteItem;
use RocketRouter\RouteParam;
use WP_REST_Request;

final class WordPressParameterResolver implements ParameterResolverInterface
{
    public function resolve(RouteItem $route, mixed $request): array
    {
        /** @var WP_REST_Request $request */
        $args = [];

        foreach ($route->params as $param) {
            $key = $param->alias ?? $param->name;

            $args[] = match ($param->source) {
                'route' => $request->get_param($key) ?? ($param->isOptional ? $param->default : null),
                'body' => $this->resolveBody($param, $request),
                'query' => $this->resolveQuery($param, $request),
            };
        }

        return $args;
    }

    private function resolveBody(RouteParam $param, WP_REST_Request $request): mixed
    {
        if ($param->type === 'array' || $param->type === 'mixed') {
            return $request->get_json_params();
        }

        return $request->get_json_params();
    }

    private function resolveQuery(RouteParam $param, WP_REST_Request $request): mixed
    {
        if ($param->type === 'array' || $param->type === 'mixed') {
            return $request->get_query_params();
        }

        return $request->get_query_params();
    }
}
