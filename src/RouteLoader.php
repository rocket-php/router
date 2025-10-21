<?php

declare(strict_types=1);

namespace RocketRouter;

use Closure;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RocketRouter\Attributes\ApiController;
use RocketRouter\Attributes\HttpMethod;
use RocketRouter\Attributes\Route;
use RuntimeException;
use SplFileInfo;

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
        private string  $projectDir,
        private Closure $serviceLocator,
        private Closure $routeRegisterer,
        ?string         $cacheFile = null
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

        $classMap = $this->scanDirectory();

        foreach ($classMap as $class => $file) {
            try {
                $reflection = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                continue;
            }

            if (!$reflection->getAttributes(ApiController::class)) {
                continue;
            }

            $classRoute = $reflection->getAttributes(Route::class);
            $routePrefix = !empty($classRoute) ? $classRoute[0]->newInstance()->route : '';

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($method->getAttributes(HttpMethod::class, ReflectionAttribute::IS_INSTANCEOF) as $attr) {
                    /**
                     * @var HttpMethod $route
                     */
                    $route = $attr->newInstance();
                    $routePath = rtrim($routePrefix, '/') . '/' . ltrim($route->route, '/');

                    $this->routes[] = [
                        'route' => $routePath,
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

    /**
     * Scan the directory for PHP files and extract classes with ApiController attribute
     */
    private function scanDirectory(): array
    {
        $classes = [];
        
        if (!is_dir($this->projectDir)) {
            return $classes;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectDir, FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $classNames = $this->extractClassNamesFromFile($file->getPathname());
            foreach ($classNames as $className) {
                $classes[$className] = $file->getPathname();
            }
        }

        return $classes;
    }

    /**
     * Extract fully qualified class names from a PHP file
     */
    private function extractClassNamesFromFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        $classes = [];
        $tokens = token_get_all($content);
        $namespace = '';

        foreach ($tokens as $i => $iValue) {
            $token = $iValue;

            if (is_array($token)) {
                [$tokenType] = $token;

                switch ($tokenType) {
                    case T_NAMESPACE:
                        $namespace = $this->extractNamespace($tokens, $i);
                        break;

                    case T_CLASS:
                    case T_INTERFACE:
                    case T_TRAIT:
                        $className = $this->extractClassName($tokens, $i);
                        if ($className) {
                            $fullClassName = $namespace ? $namespace . '\\' . $className : $className;
                            $classes[] = $fullClassName;
                        }
                        break;
                }
            }
        }

        return $classes;
    }

    /**
     * Extract namespace from tokens starting at given position
     */
    private function extractNamespace(array $tokens, int &$position): string
    {
        $namespace = '';
        $position++; // Skip T_NAMESPACE token

        // Skip whitespace
        while (isset($tokens[$position]) && is_array($tokens[$position]) && $tokens[$position][0] === T_WHITESPACE) {
            $position++;
        }

        // Collect namespace parts
        while (isset($tokens[$position])) {
            $token = $tokens[$position];

            if (is_array($token)) {
                if ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR) {
                    $namespace .= $token[1];
                } elseif ($token[0] === T_WHITESPACE) {
                    // Skip whitespace
                } else {
                    break;
                }
            } else {
                if ($token === ';') {
                    break;
                }
            }

            $position++;
        }

        return trim($namespace);
    }

    /**
     * Extract class name from tokens starting at given position
     */
    private function extractClassName(array $tokens, int &$position): string
    {
        $position++; // Skip T_CLASS token

        // Skip whitespace
        while (isset($tokens[$position]) && is_array($tokens[$position]) && $tokens[$position][0] === T_WHITESPACE) {
            $position++;
        }

        // Get class name
        if (isset($tokens[$position]) && is_array($tokens[$position]) && $tokens[$position][0] === T_STRING) {
            return $tokens[$position][1];
        }

        return '';
    }
}
