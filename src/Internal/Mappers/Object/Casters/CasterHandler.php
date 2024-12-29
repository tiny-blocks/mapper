<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use ArrayIterator;
use DateTimeInterface;
use Generator;
use ReflectionParameter;
use TinyBlocks\Collection\Collectible;
use UnitEnum;

final readonly class CasterHandler
{
    public function __construct(private ReflectionParameter $parameter)
    {
    }

    public function castValue(mixed $value): mixed
    {
        $class = $this->parameter->getType()->getName();

        $caster = match (true) {
            $class === Generator::class,                     => new GeneratorCaster(),
            $class === ArrayIterator::class,                 => new ArrayIteratorCaster(),
            is_subclass_of($class, UnitEnum::class)          => new EnumCaster(class: $class),
            is_subclass_of($class, Collectible::class)       => new CollectionCaster(class: $class),
            is_subclass_of($class, DateTimeInterface::class) => new DateTimeCaster(),
            default                                          => new DefaultCaster(class: $class)
        };

        return $caster->castValue(value: $value);
    }
}
