<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers;

use TinyBlocks\Mapper\Internal\Resolvers\StrategyResolver;
use TinyBlocks\Mapper\KeyPreservation;

final readonly class ArrayMapper
{
    public function __construct(private StrategyResolver $resolver)
    {
    }

    public function map(mixed $value, KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array
    {
        return $this->resolver
            ->resolve(value: $value)
            ->map(value: $value, keyPreservation: $keyPreservation);
    }
}
