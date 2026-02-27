#!/usr/bin/env php
<?php

/**
 * Build step — run this once at deploy time.
 * Scans controllers, resolves all attributes via reflection,
 * and writes a cache file. No reflection happens after this.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use RocketRouter\RouteScanner;
use RocketRouter\RouteCache;

$controllersDir = __DIR__ . '/Controllers';
$cacheFile      = __DIR__ . '/cache/routes.php';

$scanner = new RouteScanner($controllersDir);
$routes  = $scanner->scan();

$cache = new RouteCache($cacheFile);
$cache->write($routes);

echo "Build complete — " . count($routes) . " route(s) cached.\n\n";

foreach ($routes as $r) {
    echo "  {$r->method}\t{$r->route}\t=> {$r->controller}::{$r->action}\n";
}

echo "\nCache written to: {$cacheFile}\n";
