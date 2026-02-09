<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use ReflectionClass;

final readonly class Reflector
{
    private function __construct(private ReflectionClass $reflectionClass)
    {
    }

    public static function reflectFrom(string $class): Reflector
    {
        return new Reflector(reflectionClass: new ReflectionClass($class));
    }

    public function newInstance(array $constructorArguments): object
    {
        return $this->reflectionClass->newInstanceArgs($constructorArguments);
    }
}
