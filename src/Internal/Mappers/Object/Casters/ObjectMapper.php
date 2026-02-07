<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

final readonly class ObjectMapper
{
    public function map(iterable $iterable, string $class): object
    {
        $reflector = Reflector::reflectFrom(class: $class);
        $parameters = $reflector->getParameters();
        $inputProperties = iterator_to_array($iterable);
        $arguments = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $value = $inputProperties[$name] ?? null;

            if ($value !== null) {
                $caster = new CasterHandler(parameter: $parameter);
                $arguments[] = $caster->castValue(value: $value);
                continue;
            }

            $arguments[] = $parameter->getDefaultValue();
        }

        return $reflector->newInstance(constructorArguments: $arguments);
    }
}
