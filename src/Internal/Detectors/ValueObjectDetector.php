<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Detectors;

use ReflectionClass;
use ReflectionProperty;
use UnitEnum;

final readonly class ValueObjectDetector implements TypeDetector
{
    private const int SINGLE_PROPERTY = 1;

    public function matches(mixed $value): bool
    {
        $reflection = new ReflectionClass($value);
        $properties = $reflection->getProperties(
            ReflectionProperty::IS_PUBLIC
            | ReflectionProperty::IS_PROTECTED
            | ReflectionProperty::IS_PRIVATE
        );

        return !$value instanceof UnitEnum && count($properties) === self::SINGLE_PROPERTY;
    }
}
