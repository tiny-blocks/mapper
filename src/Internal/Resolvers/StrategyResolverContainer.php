<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Resolvers;

final class StrategyResolverContainer
{
    private StrategyResolver $resolver;

    public function get(): StrategyResolver
    {
        return $this->resolver;
    }

    public function set(StrategyResolver $resolver): void
    {
        $this->resolver = $resolver;
    }
}
