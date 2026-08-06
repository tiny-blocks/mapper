<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Metadata;

use ReflectionClass;
use ReflectionProperty;
use TinyBlocks\Mapper\Transient;

final class Properties
{
    private function __construct()
    {
    }

    private static function mergeFrom(array $collected, ReflectionClass $reflection): array
    {
        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            if (!Properties::isEligible(property: $property)) {
                continue;
            }

            if (array_key_exists($name, $collected)) {
                continue;
            }

            $collected[$name] = $property;
        }

        return $collected;
    }

    private static function isEligible(ReflectionProperty $property): bool
    {
        return !$property->isStatic() && $property->getAttributes(Transient::class) === [];
    }

    public static function collectDeclared(?ReflectionClass $reflection): array
    {
        if (is_null($reflection)) {
            return [];
        }

        $collected = [];
        $current = $reflection;

        while ($current !== false) {
            $collected = Properties::mergeFrom(collected: $collected, reflection: $current);
            $current = $current->getParentClass();
        }

        return $collected;
    }
}
