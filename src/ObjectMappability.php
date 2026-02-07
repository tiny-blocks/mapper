<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Builders\ObjectBuilder;
use TinyBlocks\Mapper\Internal\Extractors\ReflectionExtractor;
use TinyBlocks\Mapper\Internal\Factories\StrategyResolverFactory;
use TinyBlocks\Mapper\Internal\Mappers\ArrayMapper;
use TinyBlocks\Mapper\Internal\Mappers\JsonMapper;

trait ObjectMappability
{
    public static function fromIterable(iterable $iterable): static
    {
        $extractor = new ReflectionExtractor();
        $builder = new ObjectBuilder(extractor: $extractor);

        /** @var static */
        return $builder->build(iterable: $iterable, class: static::class);
    }

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
}
