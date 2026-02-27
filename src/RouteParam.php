<?php

declare(strict_types=1);

namespace RocketRouter;

final class RouteParam
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $source,
        public readonly ?string $alias,
        public readonly bool $isOptional,
        public readonly mixed $default,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function __set_state(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'],
            source: $data['source'],
            alias: $data['alias'],
            isOptional: $data['isOptional'],
            default: $data['default'],
        );
    }
}
