<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\MappabilityBehavior;

trait IterableMappability
{
    use MappabilityBehavior;

    public function getType(): string
    {
        return static::class;
    }
}
