<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

final readonly class DefaultCaster implements Caster
{
    public function __construct(private string $class)
    {
    }

    public function castValue(mixed $value): mixed
    {
        if (!class_exists(class: $this->class)) {
            return $value;
        }

        return new ObjectMapper()->map(iterable: $value, class: $this->class);
    }
}
