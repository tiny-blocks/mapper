<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use IteratorAggregate;
use Traversable;

final class Attributes extends Collection implements IteratorAggregate
{
    public function getType(): string
    {
        return 'mixed';
    }

    public function getIterator(): Traversable
    {
        yield from $this->elements;
    }
}
