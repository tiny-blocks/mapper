<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Builders\ObjectBuilder;
use TinyBlocks\Mapper\Internal\Extractors\ReflectionExtractor;
use TinyBlocks\Mapper\Internal\MappabilityBehavior;

trait ObjectMappability
{
    use MappabilityBehavior;

    public static function fromIterable(iterable $iterable): static
    {
        $extractor = new ReflectionExtractor();

        return new ObjectBuilder(extractor: $extractor)->build(iterable: $iterable, class: static::class);
    }
}
