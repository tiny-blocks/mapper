<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Strategies;

use TinyBlocks\Mapper\Internal\Extractors\ReflectionExtractor;
use TinyBlocks\Mapper\Internal\Resolvers\RecursiveValueResolver;
use TinyBlocks\Mapper\KeyPreservation;

final readonly class ComplexObjectMappingStrategy implements MappingStrategy
{
    private const int PRIORITY = 50;

    public function __construct(
        private ReflectionExtractor $extractor,
        private RecursiveValueResolver $resolver
    ) {
    }

    public function map(mixed $value, KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array
    {
        $properties = $this->extractor->extractProperties(object: $value);

        $mapped = array_map(
            fn(mixed $propertyValue): mixed => $this->resolver->resolve(
                value: $propertyValue,
                keyPreservation: $keyPreservation
            ),
            $properties
        );

        return $keyPreservation->shouldPreserveKeys()
            ? $mapped
            : array_values($mapped);
    }

    public function supports(mixed $value): bool
    {
        return is_object(value: $value);
    }

    public function priority(): int
    {
        return self::PRIORITY;
    }
}
