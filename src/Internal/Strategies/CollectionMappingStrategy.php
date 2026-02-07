<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Strategies;

use TinyBlocks\Mapper\Internal\Detectors\CollectibleDetector;
use TinyBlocks\Mapper\Internal\Resolvers\StrategyResolverContainer;
use TinyBlocks\Mapper\KeyPreservation;

final readonly class CollectionMappingStrategy implements MappingStrategy
{
    private const int PRIORITY = 90;

    public function __construct(
        private CollectibleDetector $detector,
        private StrategyResolverContainer $resolverContainer
    ) {
    }

    public function map(mixed $value, KeyPreservation $keyPreservation): array
    {
        $mapped = [];

        foreach ($value as $key => $element) {
            $strategy = $this->resolverContainer
                ->get()
                ->resolve(value: $element);
            $mapped[$key] = $strategy->map(value: $element, keyPreservation: $keyPreservation);
        }

        return $keyPreservation->shouldPreserveKeys()
            ? $mapped
            : array_values(array: $mapped);
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
