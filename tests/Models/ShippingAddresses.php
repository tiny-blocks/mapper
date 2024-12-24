<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Models;

use ArrayIterator;
use IteratorAggregate;
use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;
use Traversable;

final class ShippingAddresses implements ObjectMapper, IteratorAggregate
{
    use ObjectMappability;

    /**
     * @var \TinyBlocks\Mapper\Models\ShippingAddress[] $elements
     */
    private iterable $elements;

    public function __construct(iterable $elements = [])
    {
        $this->elements = is_array($elements) ? $elements : iterator_to_array($elements);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }
}
