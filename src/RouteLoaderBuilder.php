<?php

declare(strict_types=1);

namespace RocketRouter;

use Closure;
use Composer\Autoload\ClassLoader;

final class RouteLoaderBuilder
{
    private string $projectDir = '';

    private ?string $cacheFile = null;
    private ?Closure $serviceLocator = null;

    private ?Closure $routeRegisterer = null;

    private ClassLoader $loader;

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

    public function setLoader(ClassLoader $loader): RouteLoaderBuilder
    {
        $this->loader = $loader;
        return $this;
    }

    public function build(): RouteLoader
    {
        return new RouteLoader(
            $this->projectDir,
            $this->serviceLocator,
            $this->routeRegisterer,
            $this->loader,
            $this->cacheFile
        );
    }
}