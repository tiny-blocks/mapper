<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Strategies;

use TinyBlocks\Mapper\Internal\Detectors\ValueObjectDetector;
use TinyBlocks\Mapper\Internal\Extractors\ReflectionExtractor;
use TinyBlocks\Mapper\Internal\Resolvers\StrategyResolverContainer;
use TinyBlocks\Mapper\Internal\Transformers\ValueObjectUnwrapper;
use TinyBlocks\Mapper\KeyPreservation;

final readonly class ComplexObjectMappingStrategy implements MappingStrategy
{
    private const int PRIORITY = 50;

    public function __construct(
        private ReflectionExtractor $extractor,
        private ValueObjectUnwrapper $unwrapper,
        private StrategyResolverContainer $resolverContainer,
        private ValueObjectDetector $valueObjectDetector
    ) {
    }

    public function map(mixed $value, KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array
    {
        $properties = $this->extractor->extractProperties(object: $value);

        $mapped = array_map(function ($propertyValue) use ($keyPreservation) {
            return $this->resolveValue(value: $propertyValue, keyPreservation: $keyPreservation);
        }, $properties);

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

    private function resolveValue(mixed $value, KeyPreservation $keyPreservation): mixed
    {
        if (is_object($value) && $this->valueObjectDetector->matches(value: $value)) {
            $value = $this->unwrapper->transform(value: $value);
        }

        if (is_iterable($value) && !is_array($value)) {
            $value = iterator_to_array($value);
        }

        if (is_array(value: $value)) {
            return array_map(
                fn(mixed $item): mixed => $this->resolveValue(value: $item, keyPreservation: $keyPreservation),
                $value
            );
        }

        if (is_object($value)) {
            return $this->resolverContainer
                ->get()
                ->resolve(value: $value)
                ->map(value: $value, keyPreservation: $keyPreservation);
        }

        return $value;
    }
}
