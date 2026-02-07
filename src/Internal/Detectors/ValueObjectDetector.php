<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Detectors;

use ReflectionClass;
use ReflectionMethod;

final readonly class ValueObjectDetector implements TypeDetector
{
    private const int SINGLE_PROPERTY = 1;
    private const string VALUE_PROPERTY = 'value';

    public function matches(mixed $value): bool
    {
        $reflection = new ReflectionClass($value);
        $constructor = $reflection->getConstructor();

        return $constructor !== null && $this->hasSingleValueParameter(constructor: $constructor);
    }

    protected function hasSingleValueParameter(ReflectionMethod $constructor): bool
    {
        $parameters = $constructor->getParameters();

        return count(value: $parameters) === self::SINGLE_PROPERTY
            && $parameters[0]->getName() === self::VALUE_PROPERTY;
    }
}
