<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Resolvers;

use TinyBlocks\Mapper\Internal\Detectors\ValueObjectDetector;
use TinyBlocks\Mapper\Internal\Transformers\ValueObjectUnwrapper;
use TinyBlocks\Mapper\KeyPreservation;
use Traversable;

final readonly class RecursiveValueResolver
{
    public function __construct(
        private ValueObjectUnwrapper $unwrapper,
        private StrategyResolverContainer $resolverContainer,
        private ValueObjectDetector $valueObjectDetector
    ) {
    }

    public function resolve(mixed $value, KeyPreservation $keyPreservation): mixed
    {
        if (is_object($value) && $this->valueObjectDetector->matches(value: $value)) {
            $value = $this->unwrapper->transform(value: $value);
        }

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_array($value)) {
            $mapped = array_map(
                fn(mixed $item): mixed => $this->resolve(value: $item, keyPreservation: $keyPreservation),
                $value
            );

            return $keyPreservation->shouldPreserveKeys() ? $mapped : array_values($mapped);
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
