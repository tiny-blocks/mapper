<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Extractors;

use ReflectionClass;
use ReflectionProperty;

final readonly class ReflectionExtractor
{
    public function extractProperties(object $object): array
    {
        $reflection = new ReflectionClass(objectOrClass: $object);
        $properties = $reflection->getProperties(
            ReflectionProperty::IS_PUBLIC
            | ReflectionProperty::IS_PROTECTED
            | ReflectionProperty::IS_PRIVATE
        );

        $extracted = [];

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $name = $property->getName();
            $extracted[$name] = $property->getValue(object: $object);
        }

        return $extracted;
    }

    public function extractConstructorParameters(string $class): array
    {
        $reflection = new ReflectionClass(objectOrClass: $class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return [];
        }

        return $constructor->getParameters();
    }
}
