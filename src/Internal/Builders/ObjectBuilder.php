<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Builders;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use TinyBlocks\Mapper\Internal\Extractors\ReflectionExtractor;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\CasterHandler;

final readonly class ObjectBuilder
{
    public function __construct(private ReflectionExtractor $extractor)
    {
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @throws ReflectionException
     */
    public function build(iterable $iterable, string $class): object
    {
        $reflection = new ReflectionClass(objectOrClass: $class);
        $parameters = $this->extractor->extractConstructorParameters(class: $class);
        $inputProperties = iterator_to_array(iterator: $iterable);

        $arguments = $this->buildArguments(
            parameters: $parameters,
            inputProperties: $inputProperties
        );

        return $this->instantiate(reflection: $reflection, arguments: $arguments);
    }

    protected function buildArguments(array $parameters, array $inputProperties): array
    {
        $arguments = [];

        /** @var ReflectionParameter $parameter */
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $value = $inputProperties[$name] ?? null;

            $arguments[] = $value !== null
                ? $this->castValue(parameter: $parameter, value: $value)
                : $this->getDefaultValue(parameter: $parameter);
        }

        return $arguments;
    }

    protected function castValue(ReflectionParameter $parameter, mixed $value): mixed
    {
        $caster = new CasterHandler(parameter: $parameter);
        return $caster->castValue(value: $value);
    }

    protected function getDefaultValue(ReflectionParameter $parameter): mixed
    {
        return $parameter->isDefaultValueAvailable()
            ? $parameter->getDefaultValue()
            : null;
    }

    protected function instantiate(ReflectionClass $reflection, array $arguments): object
    {
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        if ($constructor->isPrivate()) {
            return $this->instantiateWithPrivateConstructor(
                reflection: $reflection,
                constructor: $constructor,
                arguments: $arguments
            );
        }

        return $reflection->newInstanceArgs(args: $arguments);
    }

    protected function instantiateWithPrivateConstructor(
        ReflectionClass $reflection,
        ReflectionMethod $constructor,
        array $arguments
    ): object {
        $instance = $reflection->newInstanceWithoutConstructor();
        $constructor->invokeArgs(object: $instance, args: $arguments);
        return $instance;
    }
}
