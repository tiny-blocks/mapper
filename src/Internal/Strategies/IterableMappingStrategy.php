<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Strategies;

use TinyBlocks\Mapper\Internal\Extractors\IterableExtractor;
use TinyBlocks\Mapper\Internal\Resolvers\RecursiveValueResolver;
use TinyBlocks\Mapper\IterableMapper;
use TinyBlocks\Mapper\KeyPreservation;

final readonly class IterableMappingStrategy implements ConditionalMappingStrategy
{
    public function __construct(private IterableExtractor $extractor, private RecursiveValueResolver $resolver)
    {
    }

    public function map(mixed $value, KeyPreservation $keyPreservation): array
    {
        $mapped = array_map(
            fn(mixed $item): mixed => $this->resolver->resolve(value: $item, keyPreservation: $keyPreservation),
            $this->extractor->extract(object: $value)
        );

        return $keyPreservation->shouldPreserveKeys()
            ? $mapped
            : array_values($mapped);
    }

    public function supports(mixed $value): bool
    {
        return $value instanceof IterableMapper;
    }
}
