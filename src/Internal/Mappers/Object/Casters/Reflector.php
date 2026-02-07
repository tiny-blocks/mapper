<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use ReflectionClass;
use ReflectionMethod;

final readonly class Reflector
{
    private ?ReflectionMethod $constructor;
    private array $parameters;

    public function __construct(private ReflectionClass $reflectionClass)
    {
        $this->constructor = $reflectionClass->getConstructor();
        $this->parameters = $this->constructor instanceof ReflectionMethod
            ? $this->constructor->getParameters()
            : [];
    }

    public static function reflectFrom(string $class): Reflector
    {
        return new Reflector(reflectionClass: new ReflectionClass(objectOrClass: $class));
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function newInstance(array $constructorArguments): object
    {
        $instance = $this->constructor instanceof ReflectionMethod && $this->constructor->isPrivate()
            ? $this->newInstanceWithoutConstructor()
            : $this->reflectionClass->newInstanceArgs(args: $constructorArguments);

        if ($this->constructor instanceof ReflectionMethod && $this->constructor->isPrivate()) {
            $this->constructor->invokeArgs(object: $instance, args: $constructorArguments);
        }

        return $instance;
    }

    public function newInstanceWithoutConstructor(): object
    {
        return $this->reflectionClass->newInstanceWithoutConstructor();
    }
}
