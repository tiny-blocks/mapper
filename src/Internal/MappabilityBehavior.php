<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal;

use TinyBlocks\Mapper\Internal\Factories\StrategyResolverFactory;
use TinyBlocks\Mapper\Internal\Mappers\ArrayMapper;
use TinyBlocks\Mapper\Internal\Mappers\JsonMapper;
use TinyBlocks\Mapper\KeyPreservation;

trait MappabilityBehavior
{
    public function toJson(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): string
    {
        return new JsonMapper()->map(value: $this->toArray(keyPreservation: $keyPreservation));
    }

    public function toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array
    {
        $resolver = new StrategyResolverFactory()->create();

        return new ArrayMapper(resolver: $resolver)->map(value: $this, keyPreservation: $keyPreservation);
    }
}
