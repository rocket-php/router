<?php

declare(strict_types=1);

namespace RocketRouter\Tests;

use PHPUnit\Framework\TestCase;
use RocketRouter\RouteItem;
use RocketRouter\RouteParam;

final class RouteItemTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $params = [
            new RouteParam('id', 'int', 'route', 'id', false, null),
        ];

        $item = new RouteItem(
            route: '/api/users/{id}',
            method: 'GET',
            controller: 'App\\Controllers\\UserController',
            action: 'show',
            params: $params,
        );

        $this->assertSame('/api/users/{id}', $item->route);
        $this->assertSame('GET', $item->method);
        $this->assertSame('App\\Controllers\\UserController', $item->controller);
        $this->assertSame('show', $item->action);
        $this->assertCount(1, $item->params);
        $this->assertSame('id', $item->params[0]->name);
    }

    public function testSetState(): void
    {
        $item = RouteItem::__set_state([
            'route' => '/api/posts',
            'method' => 'POST',
            'controller' => 'App\\Controllers\\PostController',
            'action' => 'store',
            'params' => [
                RouteParam::__set_state([
                    'name' => 'data',
                    'type' => 'array',
                    'source' => 'body',
                    'alias' => null,
                    'isOptional' => false,
                    'default' => null,
                ]),
            ],
        ]);

        $this->assertInstanceOf(RouteItem::class, $item);
        $this->assertSame('/api/posts', $item->route);
        $this->assertSame('POST', $item->method);
        $this->assertCount(1, $item->params);
        $this->assertInstanceOf(RouteParam::class, $item->params[0]);
        $this->assertSame('body', $item->params[0]->source);
    }
}
