<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object;

use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\CasterHandler;

final readonly class ObjectMapper
{
    public function map(iterable $iterable, string $class): mixed
    {
        $reflectionClass = Reflector::reflectFrom(class: $class);

        $parameters = $reflectionClass->getParameters();
        $inputProperties = iterator_to_array($iterable);
        $constructorArguments = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $value = $inputProperties[$name] ?? null;

            if ($value !== null) {
                $caster = new CasterHandler(parameter: $parameter);
                $castedValue = $caster->castValue(value: $value);

                $constructorArguments[] = $castedValue;
                continue;
            }

            $constructorArguments[] = $parameter->getDefaultValue();
        }

        return $reflectionClass->newInstance(constructorArguments: $constructorArguments);
    }
}
