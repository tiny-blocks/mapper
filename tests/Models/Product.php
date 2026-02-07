<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use ArrayIterator;
use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Product implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(public string $name, public Amount $amount, public ArrayIterator $stockBatch)
    {
    }
}
