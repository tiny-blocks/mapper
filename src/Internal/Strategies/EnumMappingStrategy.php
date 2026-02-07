<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Strategies;

use TinyBlocks\Mapper\Internal\Detectors\EnumDetector;
use TinyBlocks\Mapper\Internal\Transformers\EnumTransformer;
use TinyBlocks\Mapper\KeyPreservation;

final readonly class EnumMappingStrategy implements MappingStrategy
{
    private const int PRIORITY = 80;

    public function __construct(
        private EnumDetector $detector,
        private EnumTransformer $transformer
    ) {
    }

    public function map(mixed $value, KeyPreservation $keyPreservation): string|int
    {
        return $this->transformer->transform(value: $value);
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
