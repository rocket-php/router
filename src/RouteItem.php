<?php

namespace RocketRouter;

use http\Exception\RuntimeException;
use ReflectionParameter;
use RocketRouter\Attributes\FromBody;
use RocketRouter\Attributes\FromQuery;
use RocketRouter\Attributes\FromRoute;

final class RouteItem
{
    public function __construct(
        public string $route,
        public string $method,
        public string $controller,
        public string $function,
        /**
         * @var ReflectionParameter[]
         */
        public array $params
    )
    {
    }

    public function toWpRoute(): string
    {
        return preg_replace('#\{(\w+)}#', '(?P<$1>[^/]+)', $this->route);
    }

    public function resolveParameters(array $routeParms, array $jsonParams, array $queryStrParams): array
    {
        $params = [];

        foreach ($this->params as $param) {
            $pathParams = $param->getAttributes(FromRoute::class);
            if (!empty($pathParams)) {
                /** @var FromRoute $pathParam */
                $pathParam = $pathParams[0]->newInstance();
                $paramName = $pathParam->name ?? $param->getName();

                $params[] = $routeParms[$paramName] ?? null;
                continue;
            }

            $bodyParams = $param->getAttributes(FromBody::class);
            if (!empty($bodyParams)) {
                $paramType = $param->getType();

                if ($paramType === null || $paramType->getName() === 'array') {
                    $params[] = $jsonParams;
                } elseif ( class_exists($paramType)) {
                    throw new RuntimeException('Not implemented yet');
                }
                continue;
            }

            $queryParams = $param->getAttributes(FromQuery::class);
            if (!empty($queryParams)) {
                $paramType = $param->getType();

                if ($paramType === null || $paramType->getName() === 'array') {
                    $params[] = $queryStrParams;
                } elseif ( class_exists($paramType)) {
                    throw new RuntimeException('Not implemented yet');
                }
            }
        }

        return $params;
    }
}