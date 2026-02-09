<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Strategies;

use TinyBlocks\Mapper\Internal\Detectors\DateTimeDetector;
use TinyBlocks\Mapper\Internal\Transformers\DateTimeTransformer;
use TinyBlocks\Mapper\KeyPreservation;

final readonly class DateTimeMappingStrategy implements ConditionalMappingStrategy
{
    public function __construct(private DateTimeDetector $detector, private DateTimeTransformer $transformer)
    {
    }

    public function map(mixed $value, KeyPreservation $keyPreservation): string
    {
        return $this->transformer->transform(value: $value);
    }

    public function supports(mixed $value): bool
    {
        return $this->detector->matches(value: $value);
    }
}
