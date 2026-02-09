<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Resolvers;

use TinyBlocks\Mapper\Internal\Strategies\ConditionalMappingStrategy;
use TinyBlocks\Mapper\Internal\Strategies\MappingStrategy;

final class StrategyResolver
{
    private array $strategies;

    public function __construct(private readonly MappingStrategy $default, ConditionalMappingStrategy ...$strategies)
    {
        $this->strategies = $strategies;
    }

    public function resolve(mixed $value): MappingStrategy
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports(value: $value)) {
                return $strategy;
            }
        }

        return $this->default;
    }
}
