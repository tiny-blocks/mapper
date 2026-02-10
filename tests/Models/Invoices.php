<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use IteratorAggregate;
use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;
use Traversable;

final class Invoices implements IterableMapper, IteratorAggregate
{
    use IterableMappability;

    public function __construct(public array $elements)
    {
    }

    public static function createFrom(array $elements): Invoices
    {
        return new Invoices(elements: $elements);
    }

    public function getIterator(): Traversable
    {
        return yield from $this->elements;
    }
}
