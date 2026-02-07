<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Resolvers;

use TinyBlocks\Mapper\Internal\Strategies\MappingStrategy;

final class StrategyResolver
{
    private array $strategies;

    public function __construct(MappingStrategy ...$strategies)
    {
        $this->strategies = $this->sortByPriority(strategies: $strategies);
    }

    public function resolve(mixed $value): MappingStrategy
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports(value: $value)) {
                return $strategy;
            }
        }

        return end($this->strategies);
    }

    private function sortByPriority(array $strategies): array
    {
        usort(
            $strategies,
            static fn(
                MappingStrategy $current,
                MappingStrategy $next
            ): int => $next->priority() <=> $current->priority()
        );

        return $strategies;
    }
}
