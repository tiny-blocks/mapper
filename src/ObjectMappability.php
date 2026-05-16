<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Builders\ObjectBuilder;
use TinyBlocks\Mapper\Internal\MappabilityBehavior;

trait ObjectMappability
{
    use MappabilityBehavior;

    public static function fromIterable(iterable $iterable): static
    {
        return new ObjectBuilder()->build(iterable: $iterable, class: static::class);
    }
}
