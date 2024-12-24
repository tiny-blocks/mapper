<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use ArrayIterator;
use DateTimeImmutable;
use Generator;
use ReflectionProperty;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\Types\ArrayIteratorCaster;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\Types\DateTimeCaster;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\Types\DefaultCaster;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\Types\EnumCaster;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\Types\GeneratorCaster;
use UnitEnum;

final readonly class CastingHandler
{
    private string $class;

    public function __construct(private mixed $value, private ReflectionProperty $targetProperty)
    {
        $this->class = $this->targetProperty->getType()->getName();
    }

    public function applyCast(): mixed
    {
        $caster = match (true) {
            $this->class === Generator::class,            => new GeneratorCaster(),
            $this->class === ArrayIterator::class,        => new ArrayIteratorCaster(),
            $this->class === DateTimeImmutable::class,    => new DateTimeCaster(),
            is_subclass_of($this->class, UnitEnum::class) => new EnumCaster(class: $this->class),
            default                                       => new DefaultCaster(property: $this->targetProperty)
        };

        return $caster->applyCast(value: $this->value);
    }
}
