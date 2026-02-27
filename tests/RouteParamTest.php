<?php

declare(strict_types=1);

namespace RocketRouter\Tests;

use PHPUnit\Framework\TestCase;
use RocketRouter\RouteParam;

final class RouteParamTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $param = new RouteParam(
            name: 'id',
            type: 'int',
            source: 'route',
            alias: 'user_id',
            isOptional: false,
            default: null,
        );

        $this->assertSame('id', $param->name);
        $this->assertSame('int', $param->type);
        $this->assertSame('route', $param->source);
        $this->assertSame('user_id', $param->alias);
        $this->assertFalse($param->isOptional);
        $this->assertNull($param->default);
    }

    public function testOptionalParamWithDefault(): void
    {
        $param = new RouteParam(
            name: 'format',
            type: 'string',
            source: 'query',
            alias: null,
            isOptional: true,
            default: 'json',
        );

        $this->assertTrue($param->isOptional);
        $this->assertSame('json', $param->default);
        $this->assertNull($param->alias);
    }

    public function testSetState(): void
    {
        $param = RouteParam::__set_state([
            'name' => 'data',
            'type' => 'array',
            'source' => 'body',
            'alias' => null,
            'isOptional' => false,
            'default' => null,
        ]);

        $this->assertInstanceOf(RouteParam::class, $param);
        $this->assertSame('data', $param->name);
        $this->assertSame('array', $param->type);
        $this->assertSame('body', $param->source);
    }
}
