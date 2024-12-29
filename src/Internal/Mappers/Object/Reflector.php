<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object;

use ReflectionClass;
use ReflectionMethod;

final readonly class Reflector
{
    private ?ReflectionMethod $constructor;

    private array $parameters;

    private function __construct(private ReflectionClass $reflectionClass)
    {
        $this->constructor = $reflectionClass->getConstructor();
        $this->parameters = $this->constructor ? $this->constructor->getParameters() : [];
    }

    public static function reflectFrom(string $class): Reflector
    {
        return new Reflector(reflectionClass: new ReflectionClass($class));
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function newInstance(array $constructorArguments): mixed
    {
        $instance = $this->constructor && $this->constructor->isPrivate()
            ? $this->newInstanceWithoutConstructor()
            : $this->reflectionClass->newInstanceArgs($constructorArguments);

        if ($this->constructor && $this->constructor->isPrivate()) {
            $this->constructor->invokeArgs($instance, $constructorArguments);
        }

        return $instance;
    }

    public function newInstanceWithoutConstructor(): mixed
    {
        return $this->reflectionClass->newInstanceWithoutConstructor();
    }
}
