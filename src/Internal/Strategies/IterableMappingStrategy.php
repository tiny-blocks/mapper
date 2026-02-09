<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Strategies;

use TinyBlocks\Mapper\Internal\Extractors\IterableExtractor;
use TinyBlocks\Mapper\Internal\Resolvers\RecursiveValueResolver;
use TinyBlocks\Mapper\IterableMapper;
use TinyBlocks\Mapper\KeyPreservation;
use Traversable;

final readonly class IterableMappingStrategy implements MappingStrategy
{
    private const int PRIORITY = 60;

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
        return is_array($value)
            || $value instanceof Traversable
            || $value instanceof IterableMapper;
    }

    public function priority(): int
    {
        return self::PRIORITY;
    }
}
