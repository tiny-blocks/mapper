<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use ArrayIterator;
use Closure;
use DateTimeImmutable;
use Generator;
use ReflectionParameter;
use TinyBlocks\Collection\Collectible;

final readonly class CasterHandler
{
    public function __construct(private ReflectionParameter $parameter)
    {
    }

    public function castValue(mixed $value): mixed
    {
        $typeName = $this->parameter->getType()->getName();
        $caster = $this->resolveCaster(typeName: $typeName);

        return $caster->castValue(value: $value);
    }

    protected function resolveCaster(string $typeName): Caster
    {
        return match (true) {
            $typeName === Closure::class              => new ClosureCaster(),
            $typeName === Generator::class            => new GeneratorCaster(),
            $typeName === ArrayIterator::class        => new ArrayIteratorCaster(),
            $typeName === DateTimeImmutable::class    => new DateTimeCaster(),
            enum_exists($typeName)                    => new EnumCaster(class: $typeName),
            is_a($typeName, Collectible::class, true) => new CollectionCaster(class: $typeName),
            default                                   => new DefaultCaster(class: $typeName)
        };
    }
}
