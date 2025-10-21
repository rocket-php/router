<?php

declare(strict_types=1);

namespace RocketRouter;

use Closure;
use Composer\Autoload\ClassLoader;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RocketRouter\Attributes\ApiController;
use RocketRouter\Attributes\HttpMethod;
use RuntimeException;

/**
 * Route Cache Generator
 * Scans for classes with #[ApiController] attribute and generates route mappings
 */
final class RouteLoader
{
    private array $routes = [];

    private const ROUTE_CACHE_FILE = 'caches/routes.php';

    private string $cacheFile;

    public function __construct(
        private string      $projectDir,
        private Closure     $serviceLocator,
        private Closure     $routeRegisterer,
        private ClassLoader $loader,
        string              $cacheFile = null
    )
    {
        $this->cacheFile = $cacheFile ?? $projectDir . '/' . self::ROUTE_CACHE_FILE;
    }

    /**
     * Generate routes from a directory and save to an output file
     */
    public function generate(): array
    {
        $this->routes = [];

        if (!is_dir($this->projectDir)) {
            throw new \InvalidArgumentException("Directory does not exist: {$this->projectDir}");
        }

        $outputDir = dirname($this->cacheFile);
        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
            throw new RuntimeException("Cannot create output directory: {$outputDir}");
        }

        $classMap = $this->loader->getClassMap();

        foreach ($classMap as $class => $file) {
            if (!str_starts_with($class, $this->projectDir)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                continue;
            }

            if (!$reflection->getAttributes(ApiController::class)) {
                continue;
            }

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($method->getAttributes(HttpMethod::class, ReflectionAttribute::IS_INSTANCEOF) as $attr) {
                    /**
                     * @var HttpMethod $route
                     */
                    $route = $attr->newInstance();
                    $this->routes[] = [
                        'route' => $route->route,
                        'method' => $route->method,
                        'controller' => $class,
                        'function' => $method->getName(),
                    ];
                }
            }
        }

        $this->generateCacheFile();

        return $this->routes;
    }


    /**
     * Generate the cache file content
     */
    private function generateCacheFile(): void
    {
        $cacheContent = "<?php\n\n";
        $cacheContent .= "/**\n";
        $cacheContent .= " * Auto-generated route cache\n";
        $cacheContent .= " * Generated on: " . date('Y-m-d H:i:s') . "\n";
        $cacheContent .= " */\n\n";
        $cacheContent .= "return " . var_export($this->routes, true) . ";\n";

        if (file_put_contents($this->cacheFile, $cacheContent) === false) {
            throw new RuntimeException("Failed to write cache file: $this->cacheFile");
        }
    }

    public function resolve(): RouteLoader
    {
        if (!file_exists($this->cacheFile)) {
            $this->generate();
        }

        $this->readRouteFromCacheFile();

        foreach ($this->routes as $k => $route) {
            $this->routes[$k]['controller'] = ($this->serviceLocator)($route['controller']);

            if (empty($route['controller'])) {
                throw new RuntimeException("Controller not found: {$route['controller']}");
            }

            if (!method_exists($route['controller'], $route['function'])) {
                throw new RuntimeException("Method not found: {$route['function']}");
            }
        }

        return $this;
    }

    public function build(): void
    {
        $closure = $this->routeRegisterer;

        foreach ($this->routes as $route) {
            $closure($route);
        }
    }

    private function readRouteFromCacheFile(): void
    {
        if (!file_exists($this->cacheFile)) {
            throw new RuntimeException("Cache file not found: {$this->cacheFile}");
        }

        $this->routes = require $this->cacheFile;
    }
}
