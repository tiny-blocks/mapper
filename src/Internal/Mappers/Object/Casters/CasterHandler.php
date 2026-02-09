<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use Generator;
use ReflectionParameter;

final readonly class CasterHandler
{
    public function __construct(private ReflectionParameter $parameter)
    {
    }

    public function castValue(mixed $value): mixed
    {
        $typeName = $this->parameter->getType()->getName();
        $caster = match (true) {
            $typeName === Generator::class => new GeneratorCaster(),
            enum_exists($typeName)         => new EnumCaster(class: $typeName),
            default                        => new DefaultCaster(class: $typeName)
        };

        return $caster->castValue(value: $value);
    }
}
