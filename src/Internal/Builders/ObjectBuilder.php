<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Builders;

use ReflectionClass;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\CasterResolver;

final readonly class ObjectBuilder
{
    public function build(iterable $iterable, string $class): object
    {
        $reflection = new ReflectionClass(objectOrClass: $class);
        $constructor = $reflection->getConstructor();
        $inputProperties = iterator_to_array($iterable);

        if (is_null($constructor)) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (!array_key_exists($name, $inputProperties)) {
                $arguments[] = $parameter->isDefaultValueAvailable()
                    ? $parameter->getDefaultValue()
                    : null;
                continue;
            }

            $value = $inputProperties[$name];

            if (is_null($value)) {
                $arguments[] = null;
                continue;
            }

            $arguments[] = new CasterResolver(parameter: $parameter)->castValue(value: $value);
        }

        if ($constructor->isPrivate()) {
            $instance = $reflection->newInstanceWithoutConstructor();
            $constructor->invokeArgs(object: $instance, args: $arguments);

            return $instance;
        }

        return $reflection->newInstanceArgs(args: $arguments);
    }
}
