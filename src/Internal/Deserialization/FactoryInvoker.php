<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization;

use ReflectionMethod;
use ReflectionParameter;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\NamingStrategy;

final readonly class FactoryInvoker
{
    public function __construct(private NamingStrategy $naming, private ValueReader $valueReader)
    {
    }

    public function invoke(string $type, string $method, mixed $source): object
    {
        $factory = new ReflectionMethod(objectOrMethod: $type, method: $method);
        $parameters = $factory->getParameters();
        $arguments = count($parameters) === 1
            ? $this->fromScalar(source: $source, parameter: $parameters[0])
            : $this->fromArray(source: $source, parameters: $parameters);

        return $factory->invokeArgs(null, $arguments);
    }

    private function fromArray(mixed $source, array $parameters): array
    {
        if (!is_array($source)) {
            $template = 'Factory with multiple parameters requires an array source, got %s.';

            throw new UnmappableSource(message: sprintf($template, get_debug_type($source)));
        }

        $arguments = [];

        foreach ($parameters as $parameter) {
            $key = $this->naming->toSourceKey(propertyName: $parameter->getName());

            if (!array_key_exists($key, $source)) {
                $template = 'Factory parameter "%s" has no source key "%s".';

                throw new UnmappableSource(message: sprintf($template, $parameter->getName(), $key));
            }

            $arguments[$parameter->getName()] = $this->valueReader->resolve(
                value: $source[$key],
                type: $parameter->getType()
            );
        }

        return $arguments;
    }

    private function fromScalar(mixed $source, ReflectionParameter $parameter): array
    {
        return [$parameter->getName() => $this->valueReader->resolve(value: $source, type: $parameter->getType())];
    }
}
