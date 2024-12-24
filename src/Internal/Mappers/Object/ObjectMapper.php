<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object;

use ReflectionClass;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\CastingHandler;

final readonly class ObjectMapper
{
    public function map(iterable $iterable, string $class): mixed
    {
        $reflectionClass = new ReflectionClass($class);
        $properties = $reflectionClass->getProperties();
        $instance = $reflectionClass->newInstanceWithoutConstructor();

        $data = iterator_to_array($iterable);

        foreach ($properties as $property) {
            $value = $data[$property->getName()] ?? $data;

            $caster = new CastingHandler(value: $value, targetProperty: $property);
            $castedValue = $caster->applyCast();

            $property->setValue($instance, $castedValue);
        }

        return $instance;
    }
}
