<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use ArrayIterator;
use IteratorAggregate;

final readonly class Catalog implements IteratorAggregate
{
    public function __construct(public string $label, public string $reference)
    {
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator();
    }
}
