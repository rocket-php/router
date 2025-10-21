<?php

declare(strict_types=1);

namespace RocketRouter;

use Closure;

final class RouteLoaderBuilder
{
    private string $projectDir = '';

    private ?string $cacheFile = null;
    private ?Closure $serviceLocator = null;

    private ?Closure $routeRegisterer = null;

    public function setProjectDir(string $projectDir): RouteLoaderBuilder
    {
        $this->projectDir = $projectDir;
        return $this;
    }

    public function setCacheFile(string $cacheFile): RouteLoaderBuilder
    {
        $this->cacheFile = $cacheFile;
        return $this;
    }

    public function setServiceLocator(Closure $serviceLocator): RouteLoaderBuilder
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function setRouteRegisterer(Closure $routeRegisterer): RouteLoaderBuilder
    {
        $this->routeRegisterer = $routeRegisterer;
        return $this;
    }

    public function build(): RouteLoader
    {
        return new RouteLoader(
            $this->projectDir,
            $this->serviceLocator,
            $this->routeRegisterer,
            $this->cacheFile
        );
    }
}