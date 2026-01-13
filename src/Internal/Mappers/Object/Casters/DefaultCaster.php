<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use TinyBlocks\Mapper\Internal\Mappers\Object\ObjectMapper;

final readonly class DefaultCaster implements Caster
{
    public function __construct(private string $class)
    {
    }

    public function castValue(mixed $value): mixed
    {
        if (!class_exists($this->class)) {
            return $value;
        }

        return new ObjectMapper()->map(iterable: $value, class: $this->class);
    }
}
