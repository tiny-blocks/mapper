<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Strategies;

use TinyBlocks\Mapper\Internal\Detectors\ScalarDetector;
use TinyBlocks\Mapper\KeyPreservation;

final readonly class ScalarMappingStrategy implements MappingStrategy
{
    private const int PRIORITY = 10;

    public function __construct(private ScalarDetector $detector)
    {
    }

    public function map(mixed $value, KeyPreservation $keyPreservation): mixed
    {
        return $value;
    }

    public function supports(mixed $value): bool
    {
        return $this->detector->matches(value: $value);
    }

    public function priority(): int
    {
        return self::PRIORITY;
    }
}
