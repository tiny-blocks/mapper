<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Detectors;

use ReflectionClass;

final readonly class ValueObjectDetector implements TypeDetector
{
    private const int SINGLE_PROPERTY = 1;

    public function matches(mixed $value): bool
    {
        if (!is_object($value)) {
            return false;
        }

        $reflection = new ReflectionClass($value);
        $properties = $reflection->getProperties();

        return count($properties) === self::SINGLE_PROPERTY;
    }
}
