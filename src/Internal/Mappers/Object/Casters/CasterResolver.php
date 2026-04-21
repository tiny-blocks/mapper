<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use Generator;
use ReflectionNamedType;
use ReflectionParameter;

final readonly class CasterResolver
{
    public function __construct(private ReflectionParameter $parameter)
    {
    }

    public function castValue(mixed $value): mixed
    {
        $type = $this->parameter->getType();

        if (!$type instanceof ReflectionNamedType) {
            return $value;
        }

        $typeName = $type->getName();
        $caster = match (true) {
            $typeName === Generator::class => new GeneratorCaster(),
            enum_exists($typeName) => new EnumCaster(class: $typeName),
            default => new DefaultCaster(class: $typeName)
        };

        return $caster->castValue(value: $value);
    }
}
