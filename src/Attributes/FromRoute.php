<?php

namespace RocketRouter\Attributes;

use Attribute;


#[Attribute(Attribute::TARGET_PARAMETER)]
class FromRoute
{
    public function __construct(
        public string $name
    )
    {
    }
}