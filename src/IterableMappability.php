<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Factories\StrategyResolverFactory;
use TinyBlocks\Mapper\Internal\Mappers\ArrayMapper;
use TinyBlocks\Mapper\Internal\Mappers\JsonMapper;

trait IterableMappability
{
    public function toJson(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): string
    {
        $jsonMapper = new JsonMapper();
        return $jsonMapper->map(value: $this->toArray(keyPreservation: $keyPreservation));
    }

    public function toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array
    {
        $factory = new StrategyResolverFactory();
        $resolver = $factory->create();
        $arrayMapper = new ArrayMapper(resolver: $resolver);

        return $arrayMapper->map(value: $this, keyPreservation: $keyPreservation);
    }

    public function getType(): string
    {
        return static::class;
    }
}
