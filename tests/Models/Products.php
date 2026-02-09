<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;

final readonly class Products implements IterableMapper
{
    use IterableMappability;

    public function __construct(public array $items, public Country $country)
    {
    }

    public function getType(): string
    {
        return Product::class;
    }
}
