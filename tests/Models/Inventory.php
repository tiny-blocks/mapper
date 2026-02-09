<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use ArrayIterator;
use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;

final readonly class Inventory implements IterableMapper
{
    use IterableMappability;

    public function __construct(public ArrayIterator $stock)
    {
    }
}
