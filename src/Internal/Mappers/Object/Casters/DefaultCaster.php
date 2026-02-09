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
        if (!class_exists(class: $this->class) || $value instanceof $this->class) {
            return $value;
        }

        return Reflector::reflectFrom(class: $this->class)->newInstance(constructorArguments: [$value]);
    }
}
